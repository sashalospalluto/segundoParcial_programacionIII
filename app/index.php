<?php
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../vendor/autoload.php';

//require_once './db/AccesoDatos.php';
require_once '../app/middlewares/MWparaAutentificar.php';
require_once '../app/middlewares/MWparaCORS.php';


require_once './clases/usuario/UsuarioApi.php';
require_once './clases/venta/VentaApi.php';
require_once './clases/criptoMoneda/CriptoMonedaApi.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Set base path
$app->setBasePath('/app');

// Add error middleware
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Eloquent
$container=$app->getContainer();

$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'billeteravirtual',
    'username' => 'root',
    'password' => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();


$app->post('/login', \UsuarioApi::class . ':hacerLogin');
    

 //                        Usuarios
$app->group('/usuarios', function (RouteCollectorProxy $group) {

  $group->get('/{moneda}', \UsuarioApi::class . ':traerTodos')->add(\MWparaAutentificar::class . ':VerificarUsuario');
/* 
  $group->get('/{mail}', \UsuarioApi::class . ':traerUno');

  $group->post('/', \UsuarioApi::class . ':CargarUno');

  $group->delete('/{id}', \UsuarioApi::class . ':BorrarUno');

  $group->post('/modificar/{id}', \UsuarioApi::class . ':ModificarUno');

  $group->get('/generarPDF/', \UsuarioApi::class . ':GenerarPDF');

  $group->get('/generarCSV/', \UsuarioApi::class . ':GenerarCSV');*/

  }); 
    //                        Cripto monedas
$app->group('/criptomonedas', function (RouteCollectorProxy $group) {

  $group->get('/{tipo}', \CriptoMonedaApi::class . ':traerTodos')->add(\MWparaAutentificar::class . ':VerificarUsuarioRegistrado');
 
  $group->get('/traer/{id}', \CriptoMonedaApi::class . ':traerUno');//->add(\MWparaAutentificar::class . ':VerificarUsuarioVendedor');

  $group->post('/', \CriptoMonedaApi::class . ':CargarUno')->add(\MWparaAutentificar::class . ':VerificarUsuario');

  //$group->delete('/{id}', \CriptoMonedaApi::class . ':BorrarUno');//->add(\MWparaAutentificar::class . ':VerificarUsuario');

  //$group->post('/modificar/{id}', \CriptoMonedaApi::class . ':ModificarUno')->add(\MWparaAutentificar::class . ':VerificarUsuarioVendedor');

  //$group->get('/generarPDF/', \CriptoMonedaApi::class . ':GenerarPDF');

  //$group->get('/generarCSV/', \CriptoMonedaApi::class . ':GenerarCSV');
});


  //                        Ventas
  $app->group('/ventas', function (RouteCollectorProxy $group) {

    $group->get('/', \VentaApi::class . ':traerTodos')->add(\MWparaAutentificar::class . ':VerificarUsuario');
   
    //$group->get('/{id}', \VentaApi::class . ':traerUno'); 
  
    $group->post('/', \VentaApi::class . ':CargarUno')->add(\MWparaAutentificar::class . ':VerificarUsuarioRegistrado');

    /* $group->get('/generarPDF/', \VentaApi::class . ':GenerarPDF');

    $group->get('/generarCSV/', \VentaApi::class . ':GenerarCSV'); */
  });

  //$app->get('/', \VentaApi::class . ':GenerarCSV');

$app->run();
