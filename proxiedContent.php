<?php
//require the php OAuth library
require_once "lib/oauth.php";

class MyOAuthSignatureMethod_RSA_SHA1 extends OAuthSignatureMethod_RSA_SHA1 {
	protected function fetch_public_cert(&$request) {
	    $s = curl_init();
		curl_setopt($s,CURLOPT_URL,$_GET['xoauth_signature_publickey']);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, 1);
		$cert = curl_exec($s);
		curl_close($s);
		return $cert;
	}
	protected function fetch_private_cert(&$request) {
		return;
	}
}

$request = OAuthRequest::from_request();
$server = new MyOAuthSignatureMethod_RSA_SHA1();


$return = $server->check_signature($request, null, null, $_GET['oauth_signature']);

if (! $return) {
	die('invalid signature');
}

$data = json_decode(file_get_contents('php://input'), true);

?>
<script xmlns:os="http://ns.opensocial.org/2008/markup" type="text/os-data">
    <os:PeopleRequest key="Viewer" userId="@viewer" fields="name" groupId="@self"/>
    <os:PeopleRequest key="ViewerFriends" userId="@viewer" fields="name" groupId="@friends"/>
</script>
<script type="text/os-template" xmlns:os="http://ns.opensocial.org/2008/markup" require="Viewer">
    Hello ${Viewer.name.givenName} <b>${Viewer.name.familyName}</b>
</script>

<p>Hello <?= $data[0]['result']['displayName'] ?> from Proxied Content.</p>

<script type="text/os-template" xmlns:os="http://ns.opensocial.org/2008/markup" require="ViewerFriends">
    <ul>
        <li repeat="${ViewerFriends}">
            ${Cur.displayName}
        </li>
    </ul>
</script>
<script type="text/os-template" xmlns:os="http://ns.opensocial.org/2008/markup" require="highscore" autoUpdate="true">
    <h3>
        Your current HighScore: ${highscore}
        <span if="${highscore > 10}">
            Awesome!
        </span>
    </h3>
</script>

<script type="text/os-template" xmlns:os="http://ns.opensocial.org/2008/markup" require="lastAnswer" autoUpdate="true">
    <os:If condition="${lastAnswer == 1}">
        <p>Answer was correct</p>
    </os:If>
    <os:If condition="${lastAnswer != 1}">
       <p>Answer was wrong</p>
    </os:If>
</script>
<script type="text/os-template" xmlns:os="http://ns.opensocial.org/2008/markup" xmlns:abc="http://example.com/myapp" require="question" autoUpdate="true">
    <abc:question question="${question}" show_custom="true">Custom text</abc:question>
</script>
<script type="text/javascript">
    function bindAnswerLinks() {
        $('a.answer_link').unbind('click').click(function() {
            osapi.http.post({
                'href': 'http://localhost:8062/demo_game/backend/answer.php',
                'format': 'json',
                'authz' : 'signed',
                'body' : gadgets.io.encodeValues({
                    'question' : $(this).attr('id').replace('question_', ''),
                    'answer' : $(this).html()
                })
            }).execute(function(response) {
                console.log(response);
                opensocial.data.DataContext.putDataSet('lastAnswer', response.content);
                loadCurrentHighScore();
                loadQuestion();
            });
        });
        $('a.link_post_to_wall').unbind('click').click(function() {
            vz.embed.getEmbedUrl({question: $(this).attr('id').replace('link_', '')}, function(embedUrl) {
                var params = [];
                params[opensocial.Message.Field.TYPE] = opensocial.Message.Type.PUBLIC_MESSAGE;
                var message = opensocial.newMessage('Have a look: ' + embedUrl, params);
                var recipient = "VIEWER";
                opensocial.requestSendMessage(recipient, message);
            });
        });
    }
    function loadCurrentHighScore() {
        osapi.http.get({
            'href' : 'http://localhost:8062/demo_game/backend/highscore.php',
            'format' : 'json',
            'authtz' : 'signed'
        }).execute(function(response) {
            opensocial.data.DataContext.putDataSet('highscore', response.content);
        });
    }
    function loadQuestion() {
        osapi.http.get({
            'href' : 'http://localhost:8062/demo_game/backend/questions.php',
            'format' : 'json',
            'authz' : 'signed'
        }).execute(function(response) {
            opensocial.data.DataContext.putDataSet('question', response.content);
            bindAnswerLinks();
        });
    }
    gadgets.util.registerOnLoadHandler(function() {
        loadQuestion();
        loadCurrentHighScore();
    });
</script>