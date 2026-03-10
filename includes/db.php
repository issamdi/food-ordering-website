<?php
/**
 * Database connection helper
 * Includes config and provides a quick getDB() shorthand
 */

require_once __DIR__ . '/../config/config.php';

function getDB() {
    return getDBConnection();
}
