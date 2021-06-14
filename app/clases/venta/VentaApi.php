<?php
require_once 'Venta.php';
//require_once '../app/criptoMoneda/CriptoMoneda.php';
require_once '../app/interfaces/IApiUsable.php';
require_once '../app/middlewares/AutentificadorJWT.php';
require '../pdf.php';

use \App\Models\Venta as Venta;
use \App\Models\CriptoMoneda as CriptoMoneda;

class VentaApi implements IApiUsable
{

  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $id_moneda = $parametros['id_moneda'];
    $id_usuario = $parametros['id_usuario'];
    $cantidad = $parametros ['cantidad'];
    $imagen = $_FILES['imagen'];

    $nuevaVenta = new Venta();

    $nuevaVenta->id_moneda = $id_moneda;
    $nuevaVenta->id_usuario = $id_usuario;
    $nuevaVenta->cantidad = $cantidad;
    $nuevaVenta->fecha = date('Y-m-d H:i:s');
    $fecha = date('Y-m-d');

    $nuevaVenta->save();

    $extension = pathinfo($imagen['name'],PATHINFO_EXTENSION);
    //muevo la imagen a mi carpeta
    move_uploaded_file($imagen['tmp_name'],"../app/Fotos/FotosCripto/$nuevaVenta->id_moneda-$nuevaVenta->id_usuario-$fecha.$extension");
  
    $payload = json_encode(array("mensaje" => "Venta realizada"));
    $response->getBody()->write($payload);

    return $response->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    // Buscamos usuario por nombre
    $idVenta = $args['id'];

    $ventaEncontrada = Venta::where('id', $idVenta)->first();

    $payload = json_encode($ventaEncontrada);

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $cripto = CriptoMoneda::where('nacionalidad','=','alemania')->first();

    $min= '2021-06-10';
    $max ='2021-06-13';

    $lista = Venta::where('fecha','>=',$min)
                    ->where('fecha', '<=',$max)
                    ->where('id_moneda','=',$cripto->id)->get(); 

      /* $lista = Venta::where('fecha','>=',$min)
                    ->where('fecha', '<=',$max)->get();  */            

    $payload = json_encode(array("listaVentas" => $lista));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json'); 
  }

  public function ModificarUno($request, $response, $args)
  {
    /* $parametros = $request->getParsedBody();
    
    $usuarioId = $args['id'];

    $mail = $parametros['mail'];
    $clave = $parametros['clave'];
    $sexo = $parametros['sexo'];
    $tipo = $parametros ['tipo'];

    $usuarioAModificar = Usuario::where ('id', '=', $usuarioId)->first();

    if($usuarioAModificar!==null)
    {
        $usuarioAModificar->mail=$mail;
        $usuarioAModificar->clave=$clave;
        $usuarioAModificar->sexo = $sexo;
        $usuarioAModificar->tipo = $tipo;

        $usuarioAModificar->save();
        $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));
    }
    else
    {
        $payload = json_encode(array("mensaje" => "Usuario no encontrado"));
    } */

    $response->getBody()->write("no se pueden modificar las ventas");
    return $response->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
    $ventaId = $args['id'];
    // Buscamos el usuario
    $usuario = Venta::find($ventaId);
    // Borramos
    $usuario->delete();

    $payload = json_encode(array("mensaje" => "Venta borrada con exito"));

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
  
    $todasLasVentas = Venta::all();
    $total=Venta::all()->count();
    //$venta = Venta::find(1);

    $pdf->Cell(30,10,'id',1,0,'C',0);
    $pdf->Cell(30,10,'cliente',1,0,'C',0);
    $pdf->Cell(30,10,'id_hortaliza',1,0,'C',0);
    $pdf->Cell(30,10,'id_empleado',1,0,'C',0);
    $pdf->Cell(30,10,'cantidad',1,0,'C',0);
    $pdf->Cell(30,10,'fecha',1,1,'C',0);
    
    if($total >1)
    {
      foreach($todasLasVentas as $venta)
      {
        $pdf->Cell(30,10,$venta->id,1,0,'C',0);
        $pdf->Cell(30,10,$venta->cliente,1,0,'C',0);
        $pdf->Cell(30,10,$venta->id_hortaliza,1,0,'C',0);
        $pdf->Cell(30,10,$venta->id_empleado,1,0,'C',0);
        $pdf->Cell(30,10,$venta->cantidad,1,0,'C',0);
        $pdf->Cell(30,10,$venta->fecha,1,1,'C',0);
      }
    }
    else
    {
      $pdf->Cell(30,10,$todasLasVentas->id,1,0,'C',0);
      $pdf->Cell(30,10,$todasLasVentas->cliente,1,0,'C',0);
      $pdf->Cell(30,10,$todasLasVentas->id_hortaliza,1,0,'C',0);
      $pdf->Cell(30,10,$todasLasVentas->id_empleado,1,0,'C',0);
      $pdf->Cell(30,10,$todasLasVentas->cantidad,1,0,'C',0);
      $pdf->Cell(30,10,$todasLasVentas->fecha,1,1,'C',0);
    }

    $pdf->Output();

    return $response;
  }

  public function GenerarCSV ($request, $response, $args)
  {
    $archivo = fopen("../archivosGenerados/ventas.csv",'w');

    $todasLasVentas = Venta::all();

    if($archivo)
    {
      foreach($todasLasVentas as $venta)
      {
        $datos = $venta ->id .",". $venta ->cliente . "," . $venta ->id_hortaliza . "," . $venta ->id_empleado ."," . $venta ->cantidad . "," . $venta ->fecha ."\n";
        
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