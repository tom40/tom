<?php

/**
 * Class to store and retrieve the version of the application
 *
 * @category   App
 * @package    App_Version
 * @copyright  Copyright (c) 2012 Take Note Typing
 * @version    $Id: Version.php 197 2012-08-23 17:24:04Z davidcarter $
 */
final class App_Version
{
    /**
     * Application version identification
     */
    const VERSION       = '1.5';
    const VERSION_STAGE = '1.5';
    const VERSION_DEV   = '1.5';
    
    /**
     * Return the version string.
     *
     * @return string
     */
    public static function getVersion()
    {
        $version = self::VERSION;

        if ( 'testing' === APPLICATION_ENV )
        {
            $version = self::VERSION_STAGE . self::_getRevisionNumber();
        }
        elseif ( 'development' === APPLICATION_ENV )
        {
            $version = self::VERSION_DEV . self::_getRevisionNumber();
        }
        return $version;
    }

    /**
     * Get revision number
     *
     * @return string
     */
    protected static function _getRevisionNumber()
    {
        $revision     = '';
        $revisionFile = APPLICATION_PATH . '/../.revision';

        if ( file_exists( $revisionFile ) )
        {
            $data     = file( $revisionFile );
            $revision = ' r' . $data[0];
        }
        return $revision;
    }

}
