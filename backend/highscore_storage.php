<?php

class HighScoreStorage {
    /**
     *
     * @var SQLite3
     */
    private $_db;

    public function  __construct() {
        $this->_db = new SQLite3('highscore.db');

        $this->initTable();
    }

    protected function initTable() {
        $result = $this->_db->query("SELECT name FROM sqlite_master WHERE name='highscore' AND type='table'");
        if (!$result->fetchArray(SQLITE3_ASSOC)) {
            $result = $this->_db->query("CREATE TABLE `highscore` (
              `user_id` varchar(100) NOT NULL DEFAULT '',
              `score` int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`user_id`)
            )");
        }
    }

    public function get($userId) {
        $stmt = $this->_db->prepare("SELECT score FROM highscore where user_id = :user_id");
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $result = $result->fetchArray(SQLITE3_ASSOC);
        if (! $result) {
            return 0;
        }
        return $result['score'];
    }

    public function getHighScores() {
        $stmt = $this->_db->prepare("SELECT score, user_id FROM highscore ORDER BY score DESC LIMIT 10");
        $result = $stmt->execute();
        $result = $result->fetchArray(SQLITE3_ASSOC);
        return $result;
    }

    public function increment($userId) {
        $oldScore = $this->get($userId);
        $stmt = $this->_db->prepare("REPLACE INTO highscore (user_id, score) VALUES (:user_id, :score)");
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':score', $oldScore + 1, SQLITE3_INTEGER);
        $stmt->execute();
    }
}
