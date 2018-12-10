<?
spl_autoload_register( "Autoloader" );

function Autoloader( $class ) {
    //var_dump($class."<br>");
    if(preg_match('/Running/', $class))
        $class = str_replace('Running', 'v'.CURRENT_VERSION, $class);
    $file = ROOT_DIR . str_replace('\\', '/', str_replace( "Dot\\", '', $class ) ) . ".php";
    //var_dump($file."<br>");
    //var_dump(is_readable( $file ));
    if ( is_readable( $file ) ) require_once( $file );
}