<?php

Yii::setPathOfAlias('local', dirname(APPLICATION_PATH));

return CMap::mergeArray(
        array(
        'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
        'commandMap' => array(
            'migrate' => array(
                'class' => 'system.cli.commands.MigrateCommand',
                'migrationTable' => 'yii_migration',
                'migrationPath' => 'local.migrate.migrations'
            )
        ),
        'components' => array(
            'db' => array(
                'connectionString' => 'mysql:host=' . DB_HOST . ';dbname=' . DB_DATABSE,
                'username' => DB_USER,
                'password' => DB_PASSWORD,
            ),
        )
        ), array()
);
