<?php

require_once "AutentificadorJWT.php";

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response;

class MWparaAutentificar
{
 /**
   * @api {any} /MWparaAutenticar/  Verificar Usuario
   * @apiVersion 0.1.0
   * @apiName VerificarUsuario
   * @apiGroup MIDDLEWARE
   * @apiDescription  Por medio de este MiddleWare verifico las credeciales antes de ingresar al correspondiente metodo 
   *
   * @apiParam {ServerRequestInterface} request  El objeto REQUEST.
 * @apiParam {ResponseInterface} response El objeto RESPONSE.
 * @apiParam {Callable} next  The next middleware callable.
   *
   * @apiExample Como usarlo:
   *    ->add(\MWparaAutenticar::class . ':VerificarUsuario')
   */

	public function VerificarUsuario(Request $request, RequestHandler $handler) 
	{
         
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta="";

		$header = $request->getHeaderLine('Authorization');
		$token = trim(explode("Bearer", $header)[1]);

		try 
		{
			//$token="";
			AutentificadorJWT::verificarToken($token);
			$objDelaRespuesta->esValido=true;      
		}
		catch (Exception $e) {      
			//guardar en un log
			$objDelaRespuesta->excepcion=$e->getMessage();
			$objDelaRespuesta->esValido=false;     
		}

		if($objDelaRespuesta->esValido)
		{						
			$payload=AutentificadorJWT::ObtenerData($token);
			//var_dump($payload);
			// DELETE,PUT y DELETE sirve para todos los logeados y admin
			if($payload->perfil=="administrador")
			{
				//$response = $next($request, $response);
				$response = $handler->handle($request);
			}		           	
			else
			{	
				$objDelaRespuesta->respuesta="Solo administradores";
			}      
		}    
		else
		{
			//   $response->getBody()->write('<p>no tenes habilitado el ingreso</p>');
			$objDelaRespuesta->respuesta="Solo usuarios registrados";
			$objDelaRespuesta->elToken=$token;
		}  
			  
		if($objDelaRespuesta->respuesta!="")
		{
			$payload = json_encode($objDelaRespuesta);
			$response = new Response();
			$response->getBody()->write($payload);

			return $response->withHeader('Content-Type', 'application/json');
		}
		  
		 //$response->getBody()->write('<p>vuelvo del verificador de credenciales</p>');
		 return $response;  
	} 

	public function VerificarUsuarioRegistrado(Request $request, RequestHandler $handler) 
	{
         
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta="";

		$header = $request->getHeaderLine('Authorization');
		$token = trim(explode("Bearer", $header)[1]);

		try 
		{
			//$token="";
			AutentificadorJWT::verificarToken($token);
			$objDelaRespuesta->esValido=true;      
		}
		catch (Exception $e) {      
			//guardar en un log
			$objDelaRespuesta->excepcion=$e->getMessage();
			$objDelaRespuesta->esValido=false;     
		}

		if($objDelaRespuesta->esValido)
		{						
			$payload=AutentificadorJWT::ObtenerData($token);
			//var_dump($payload);
			// DELETE,PUT y DELETE sirve para todos los logeados y admin
			if($payload->perfil=="administrador" || $payload->perfil=="cliente" )
			{				
				$response = $handler->handle($request);
			}		           	
			else
			{	
				$objDelaRespuesta->respuesta="Solo usuarios registrados";
			}      
		}    
		else
		{
			//   $response->getBody()->write('<p>no tenes habilitado el ingreso</p>');
			$objDelaRespuesta->respuesta="Solo usuarios registrados";
			$objDelaRespuesta->elToken=$token;
		}  
			  
		if($objDelaRespuesta->respuesta!="")
		{
			$payload = json_encode($objDelaRespuesta);
			$response = new Response();
			$response->getBody()->write($payload);

			return $response->withHeader('Content-Type', 'application/json');
		}
		  
		 //$response->getBody()->write('<p>vuelvo del verificador de credenciales</p>');
		 return $response;  
	} 
}