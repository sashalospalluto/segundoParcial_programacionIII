<?php
require_once 'Usuario.php';
//require_once '../apirest/clases/IApiUsable.php';
require_once '../app/interfaces/IApiUsable.php';
require_once '../app/middlewares/AutentificadorJWT.php';

use App\Models\CriptoMoneda;
use \App\Models\Usuario as Usuario;
use \App\Models\Venta as Venta;

class UsuarioApi implements IApiUsable
{

  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $mail = $parametros['mail'];
    $clave = $parametros['clave'];
    $tipo = $parametros ['tipo'];

    $nuevoUsuario = new Usuario();

    $nuevoUsuario->mail = $mail;
    $nuevoUsuario->clave = $clave;
    $nuevoUsuario->tipo = $tipo;

    $nuevoUsuario->save();
  
    $payload = json_encode(array("mensaje" => "Usuario creado con exito"));
    $response->getBody()->write($payload);

    return $response->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    // Buscamos usuario por nombre
    $usr = $args['mail'];

    $usuario = Usuario::where('mail', $usr)->first();

    $payload = json_encode($usuario);

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $monedaAbuscar=$args['moneda'];
    $moneda=CriptoMoneda::where('nombre','=',$monedaAbuscar)->first();

    $listaVentas= Venta::where('id_moneda','=',$moneda->id)->distinct()->get();

    $arrayObjetos = array();

    foreach($listaVentas as $ventas)
    {
      $dato = Usuario::where('id','=',$ventas->id_usuario)->first();
      array_push($arrayObjetos,$dato);
    }

    $payload = json_encode(array("listaUsuario" => $arrayObjetos));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    
    $usuarioId = $args['id'];

    $mail = $parametros['mail'];
    $clave = $parametros['clave'];
    $tipo = $parametros ['tipo'];

    $usuarioAModificar = Usuario::where ('id', '=', $usuarioId)->first();

    if($usuarioAModificar!==null)
    {
        $usuarioAModificar->mail=$mail;
        $usuarioAModificar->clave=$clave;
        $usuarioAModificar->tipo = $tipo;

        $usuarioAModificar->save();
        $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));
    }
    else
    {
        $payload = json_encode(array("mensaje" => "Usuario no encontrado"));
    }

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
    $usuarioId = $args['id'];
    // Buscamos el usuario
    $usuario = Usuario::find($usuarioId);
    // Borramos
    $usuario->delete();

    $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public  function hacerLogin($request, $response, $args) 
    {
        //$objDelaRespuesta= new stdclass();
        $parametros = $request->getParsedBody();
        //var_dump($ArrayDeParametros);
        $mail= $parametros['mail'];
        $clave= $parametros['clave'];
        $tipo= $parametros['tipo'];

        $miUsuario = Usuario::where('mail','=' ,$mail)
                            ->where('clave','=',$clave)
                            ->where('tipo', '=',$tipo)->first();  
                            
        if($miUsuario!=null)
        {
          $datos = array('mail' => $miUsuario->mail,'perfil' => $miUsuario->tipo);
  
          $token= AutentificadorJWT::CrearToken($datos); 
  
          $payload = json_encode(array("TOKEN" => $token));
        }
        else
        {
          $payload = json_encode(array("error" => "usuario no registrado"));
        }


        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function GenerarPDF ($request, $response, $args)
  {
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Times','',12);
  
    $todos = Usuario::all();
    $total=Usuario::all()->count();
    //$venta = Venta::find(1);

    $pdf->Cell(30,10,'id',1,0,'C',0);
    $pdf->Cell(60,10,'mail',1,0,'C',0);
    $pdf->Cell(30,10,'tipo',1,1,'C',0);
    
    if($total >1)
    {
      foreach($todos as $unoSolo)
      {
        $pdf->Cell(30,10,$unoSolo->id,1,0,'C',0);
        $pdf->Cell(60,10,$unoSolo->mail,1,0,'C',0);
        $pdf->Cell(30,10,$unoSolo->tipo,1,1,'C',0);
      }
    }
    else
    {
      $pdf->Cell(30,10,$todos->id,1,0,'C',0);
      $pdf->Cell(60,10,$todos->mail,1,0,'C',0);
      $pdf->Cell(30,10,$todos->tipo,1,1,'C',0);
    }

    $pdf->Output();

    return $response;
  }

  public function GenerarCSV ($request, $response, $args)
  {
    $archivo = fopen("../archivosGenerados/Usuarios.csv",'w');

    $todos = Usuario::all();

    if($archivo)
    {
      foreach($todos as $unoSolo)
      {
        $datos = $unoSolo ->id .",". $unoSolo ->mail . "," . $unoSolo ->tipo ."\n";
        
        fputs($archivo , $datos);
      }

      $response->getBody()->write("archivo guardado");
      fclose($archivo); 
    }
    else
    {
      $response->getBody()->write("no se pudo guardar el archivo");
    }
    
    return $response
    ->withHeader('Content-Type', 'application/json');
  }
}

?>