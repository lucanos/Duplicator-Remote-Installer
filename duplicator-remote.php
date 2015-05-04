<?php

// Global Configuration
set_time_limit( 0 );
error_reporting( E_ALL );

// Version Information
define( 'WPRI_VERSION' , '0.2' );

$errors = array();

// URL Patterns
$url_ZIP = 'http://%1$s/wp-snapshots/%2$s_archive.zip';
$url_INS = 'http://%1$s/wp-snapshots/%2$s_installer.php?get=1&file=%2$s_installer.php';


function downloadFromURL( $url = null , $local = null ){
  $result = null;
  if( is_null( $local ) )
    $local = basename( $url );
  if( $content = @file_get_contents( $url ) ){
    $result = @file_put_contents( $local , $content );
  }elseif( function_exists( 'curl_init' ) ){
    $fp = fopen( dirname(__FILE__) . '/' . $local , 'w+' );
    $ch = curl_init( str_replace( ' ' , '%20' , $url ) );
    curl_setopt($ch , CURLOPT_TIMEOUT        , 50 );
    curl_setopt($ch , CURLOPT_FILE           , $fp );
    curl_setopt($ch , CURLOPT_FOLLOWLOCATION , true );
    $result = curl_exec( $ch );
    curl_close( $ch );
    fclose( $fp );
  }else{
    $result = false;
  }
  return $result;
}
function getGithubVersion(){
  $versionURL = 'https://lucanos.github.io/Duplicator-Remote-Installer/version.txt';
  $remoteVersion = null;
  if( !( $remoteVersion = @file_get_contents( $versionURL ) )
      && function_exists( 'curl_init' ) ){
    $fp = fopen( dirname(__FILE__) . '/' . $local , 'w+' );
    $ch = curl_init( str_replace( ' ' , '%20' , $url ) );
    curl_setopt($ch , CURLOPT_TIMEOUT        , 50 );
    curl_setopt($ch , CURLOPT_FILE           , $fp );
    curl_setopt($ch , CURLOPT_FOLLOWLOCATION , true );
    $remoteVersion = curl_exec( $ch );
    curl_close( $ch );
    fclose( $fp );
  }
  return $remoteVersion;
}

// Declare Parameters
$step = 0;
if( isset( $_POST['step'] ) )
  $step = (int) $_POST['step'];

if( $step = 2 ){
  if( !isset( $_POST['domain'] ) || !$_POST['domain'] ){
    $errors[] = 'No Domain Set';
  }
  if( !isset( $_POST['prefix'] ) || !$_POST['prefix'] ){
    $errors[] = 'No Domain Set';
  }elseif( !preg_match( '/\_[0-9a-f]{29}$/i' , $_POST['prefix'] ) ) {
    $errors[] = 'Invalid File Prefix';
  }
  if( $errors ){
    $step = 1;
  }
}

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
<meta name="viewport" content="width=device-width">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Duplicator &gt; Remote Installer</title>
<link rel="stylesheet" id="combined-css" href="//lucanos.github.io/Duplicator-Remote-Installer/stylesheets/combined.css" type="text/css" media="all">
</head>
<body class="wp-core-ui">
<h1 id="logo"><a href="http://wordpress.org/">Duplicator Remote Installer</a></h1>

<?php

switch( $step ){

  default :
  case 0 :

?>
<!-- STEP 0 //-->
<h1>Duplicator Remote Installer</h1>
<p>The Duplicator Remote Installer is a script designed to streamline the installation of the WordPress Content Management System. Some users have limited experience using FTP, some webhosts do not allow files to be decompressed after being uploaded, and some people want to make their WordPress installs faster and simpler.</p>
<p>Using the Duplicator Remote Installer is simple - upload a single PHP file to your server, access it via a web-browser and simply follow the prompts through 7 easy steps, at the end of which, the Wordpress Installer will commence.</p>
<?php
    if( version_compare( WPRI_VERSION , $githubVersion = getGithubVersion() , '<' ) ){
?>
<p class="version_alert">You are using Version <?php echo WPRI_VERSION; ?>. Version <?php echo $githubVersion; ?> is available through <a href="https://github.com/lucanos/WordPress-Remote-Installer">Github</a>.</p>
<?php
    }
?>
<form method="post">
  <input type="hidden" name="step" value="1" />
  <input type="submit" name="submit" value="Let's Get Started!" class="button button-large" />
</form>
<?php

    break;

  case 1 :
?>
<!-- STEP 1 //-->
<h1>Step 1/2: Download Details</h1>
<p>ZIP URL or Domain and File Prefix</p>
<form method="post">
  <table class="form-table">
    <tbody><tr>
      <th scope="row"><label for="url_zip">URL for ZIP File</label></th>
      <td><input name="url_zip" id="url_zip" type="text" size="25" value="<?php echo ( isset( $_POST['url_zip'] ) ? $_POST['url_zip'] : '' ); ?>"></td>
      <td>The name of the database you want to run WP in.</td>
    </tr>
    <tr>
      <th scope="row">OR</th>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
      <th scope="row"><label for="url_domain">Domain</label></th>
      <td><input name="url_domain" id="url_domain" type="text" size="25" value=""></td>
      <td>Your MySQL username</td>
    </tr>
    <tr>
      <th scope="row"><label for="url_prefix">Password</label></th>
      <td><input name="url_prefix" id="url_prefix" type="text" size="25" value=""></td>
      <td>…and your MySQL password.</td>
    </tr>
  </tbody></table>
  <input type="hidden" name="step" value="2" />
  <input type="submit" name="submit" value="Commence Install of WordPress" class="button button-large" />
</form>
<?php

    break;

  case 2 :

?>
<!-- STEP 2 //-->
<h1>Step 2/2: Installing Wordpress</h1>
<ul>
<?php
    $proceed = true;

    if( downloadFromURL( 'https://wordpress.org/latest.zip' , 'wordpress.zip' ) ){
?>
  <li class="pass">Downloading Latest WordPress from Wordpress.org - OK</li>
<?php
    }else{
      $proceed = false;
?>
  <li class="fail">Downloading Latest WordPress from Wordpress.org - FAILED</li>
<?php
    }

    if( !$proceed ){
?>
  <li class="skip">Extract WordPress - SKIPPED</li>
<?php
    }elseif( extractSubFolder( 'wordpress.zip' , null , 'wordpress' ) ){
?>
  <li class="pass">Extract WordPress - OK</li>
<?php
    }else{
      $proceed = false;
?>
  <li class="fail">Extract WordPress - FAILED</li>
<?php
    }

    if( !$proceed ){
?>
  <li class="skip">Delete WordPress ZIP - SKIPPED</li>
<?php
    }elseif( unlink( 'wordpress.zip' ) ){
?>
  <li class="pass">Delete WordPress ZIP - OK</li>
<?php
    }else{
      $proceed = false;
?>
  <li class="fail">Delete WordPress ZIP - FAILED</li>
<?php
    }
?>
</ul>
<?php

    if( !$proceed ){
?>
<p>NOTE: We are unable to proceed until the above issue(s) are resolved.</p>
<?php
    }else{
?>
<form method="post">
  <input type="hidden" name="step" value="3" />
  <input type="submit" name="submit" value="Next Step - Plugins" class="button button-large" />
</form>
<?php
    }

    break;

  case 3 :
  
    $suggest = '';
    if( is_array( $suggestions['plugins'] ) ){
      $suggest = implode( "\n" , $suggestions['plugins'] );
    }elseif( is_string( $suggestions['plugins'] ) ){
      if( !( $suggest = @file_get_contents( $suggestions['plugins'] ) ) )
        $suggest = '';
    }

?>
<!-- STEP 3 //-->
<h1>Step 3/7: Installing Plugins</h1>
<p>List the Download URLs for all WordPress Plugins, one per line</p>
<form method="post">
  <textarea name="plugins"><?php echo $suggest; ?></textarea>
  <input type="hidden" name="step" value="4" />
  <input type="submit" name="submit" value="Install Plugins" class="button button-large" />
</form>
<?php

    break;

  case 4 :

?>
<!-- STEP 4 //-->
<h1>Step 4/7: Installing Plugins</h1>
<ul>
<?php
    $plugin_result = ( !file_exists( @unlink( dirname( __FILE__ ).'/wp-content/plugins/hello.php' ) || dirname( __FILE__ ).'/wp-content/plugins/hello.php' ) );
?>
  <li class="<?php echo ( $plugin_result ? 'pass' : 'fail' ); ?>">Delete Unneeded "Hello Dolly" Plugin - <?php echo ( $plugin_result ? 'OK' : 'FAILED' ); ?></li>
<?php    
    if( isset( $_POST['plugins'] ) ){
      $plugins = explode( "\n" , $_POST['plugins'] );
      foreach( $plugins as $url ){
        $plugin_result = false;
        $plugin_message = 'UNKNOWN';
        $url = trim( $url );
        if( strpos( $url , 'http' )!==0 )
          $url = 'http://'.$url;
        if( preg_match( '/^(http?\:\/\/?downloads\.wordpress\.org\/plugin\/)([^\.]+)((?:\.\d+)+)?\.zip$/' , $url , $bits ) )
          $url = $bits[1].$bits[2].'.zip';
        $get = @file_get_contents( $url );
        if( !$get ){
          $plugin_message = 'FAILED TO DOWNLOAD';
        }else{
          file_put_contents( 'temp_plugin.zip' , $get );
          if( !extractSubFolder( 'temp_plugin.zip' , dirname( __FILE__ ).'/wp-content/plugins' ) ){
            $plugin_message = 'FAILED TO EXTRACT';
          }else{
            $plugin_result = true;
            $plugin_message = 'OK';
          }
          @unlink( 'temp_plugin.zip' );
        }
?>
  <li class="<?php echo ( $plugin_result ? 'pass' : 'fail' ); ?>">Installing <strong><?php echo $bits[2]; ?></strong> - <?php echo $plugin_message; ?></li>
<?php
      }
    }
?>
</ul>
<form method="post">
  <input type="hidden" name="step" value="5" />
  <input type="submit" name="submit" value="Next Step - Themes" class="button button-large" />
</form>
<?php

    break;

  case 5 :
  
    $suggest = '';
    if( is_array( $suggestions['themes'] ) ){
      $suggest = implode( "\n" , $suggestions['themes'] );
    }elseif( is_string( $suggestions['themes'] ) ){
      if( !( $suggest = @file_get_contents( $suggestions['themes'] ) ) )
        $suggest = '';
    }

?>
<!-- STEP 5 //-->
<h1>Step 5/7: Installing Themes</h1>
<p>List the Download URLs for all WordPress Themes, one per line</p>
<form method="post">
  <textarea name="themes"><?php echo $suggest; ?></textarea>
  <input type="hidden" name="step" value="6" />
  <input type="submit" name="submit" value="Install Themes" class="button button-large" />
</form>
<?php

    break;

  case 6 :

?>
<!-- STEP 6 //-->
<h1>Step 6/7: Installing Themes</h1>
<ul>
<?php

    if( isset( $_POST['themes'] ) ){
      $themes = explode( "\n" , $_POST['themes'] );
      foreach( $themes as $url ){
        $theme_result = false;
        $theme_message = 'UNKNOWN';
        $url = trim( $url );
        if( !$url ) continue;
        if( strpos( $url , 'http' )!==0 )
          $url = 'http://'.$url;
        preg_match( '/^(http?\:\/\/?wordpress.org\/extend\/themes\/download\/)([^\.]+)((?:\.\d+)+)\.zip$/' , $url , $bits );
        $get = @file_get_contents( $url );
        if( !$get ){
          $theme_message = 'FAILED TO DOWNLOAD';
        }else{
          file_put_contents( 'temp_theme.zip' , $get );
          if( !extractSubFolder( 'temp_theme.zip' , dirname( __FILE__ ).'/wp-content/themes' ) ){
            $theme_message = 'FAILED TO EXTRACT';
          }else{
            $theme_result = true;
            $theme_message = 'OK';
          }
?>
  <li class="<?php echo ( $theme_result ? 'pass' : 'fail' ); ?>">Installing <strong><?php echo $bits[2]; ?>.zip</strong> - <?php echo $theme_message; ?></li>
<?php
          @unlink( 'temp_theme.zip' );
        }
        echo '</li>';
      }
    }

?>
</ul>
<form method="post">
  <input type="hidden" name="step" value="7" />
  <input type="submit" name="submit" value="Next Step - Clean Up" class="button button-large" />
</form>
<?php

    break;

  case 7 :

?>
<!-- STEP 7 //-->
<h1>Step 7/7: Cleaning Up</h1>
<ul>
<?php

    $tests = array(
      array(
        'result' => ( !file_exists( 'wordpress.zip' ) || @unlink( 'wordpress.zip' ) ) ,
        'pass' => 'Remove WordPress Installer - OK' ,
        'fail' => 'Remove WordPress Installer - FAILED'
      ) ,
      array(
        'result' => ( !file_exists( 'temp_plugin.zip' ) || @unlink( 'temp_plugin.zip' ) ) ,
        'pass' => 'Remove Temporary Plugin File - OK' ,
        'fail' => 'Remove Temporary Plugin File - FAILED'
      ) ,
      array(
        'result' => ( !file_exists( 'temp_theme.zip' ) || @unlink( 'temp_theme.zip' ) ) ,
        'pass' => 'Remove Temporary Theme File - OK' ,
        'fail' => 'Remove Temporary Theme File - FAILED'
      ) ,
      array(
        'result' => ( !file_exists( __FILE__ ) || @unlink( __FILE__ ) ) ,
        'pass' => 'Remove Duplicator Remote Installer - OK' ,
        'fail' => 'Remove Duplicator Remote Installer - FAILED'
      ) ,
    );
    
    foreach( $tests as $t ){
?>
  <li class="<?php echo ( $t['result'] ? 'pass' : 'fail' ); ?>"><?php echo $t[( $t['result'] ? 'pass' : 'fail' )]; ?></li>
<?php
    }
?>
</ul>
<form method="post" action="./wp-admin/setup-config.php">
  <input type="submit" name="submit" value="Launch WordPress Installer" class="button button-large" />
</form>
<?php

    break;
}

?>

<div id="footer">
  <a href="https://github.com/lucanos/WordPress-Remote-Installer" class="github">View on GitHub</a>
  Created by <a href="http://lucanos.com">Luke Stevenson</a><br/>
  <div class="legal">
    <strong>NOTE:</strong> This script is not an official WordPress product.<br/>
    The WordPress logo is the property of the WordPress Foundation.
  </div>
</div>

<script src="//code.jquery.com/jquery.min.js"></script>
<script src="//lucanos.github.io/WordPress-Remote-Installer/javascripts/installer.js"></script>
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
ga('create', 'UA-238524-33');
ga('send', 'pageview', 'step<?php echo $step; ?>');
ga('send', 'event', 'step', '<?php echo $step; ?>');
</script>
</body>
</html>