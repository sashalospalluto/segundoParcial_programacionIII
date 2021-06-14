<?php
require_once 'CriptoMoneda.php';

require_once '../app/interfaces/IApiUsable.php';
require_once '../app/middlewares/AutentificadorJWT.php';

use \App\Models\CriptoMoneda as CriptoMoneda;

class CriptoMonedaApi implements IApiUsable
{

  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $precio = $parametros['precio'];
    $nombre = $parametros['nombre'];
    $nacionalidad = $parametros ['nacionalidad'];
    $imagen = $_FILES['imagen'];

    $nuevaCripto= new CriptoMoneda();

    $nuevaCripto->precio = $precio;
    $nuevaCripto->nombre = $nombre;
    $nuevaCripto->nacionalidad = $nacionalidad;

    $nuevaCripto->save();
  
    $payload = json_encode(array("mensaje" => "Cripto moneda creada con exito"));
    
    $response->getBody()->write($payload);
    
    $extension = pathinfo($imagen['name'],PATHINFO_EXTENSION);
    //muevo la imagen a mi carpeta
    move_uploaded_file($imagen['tmp_name'],"../app/Fotos/CriptoMonedas/$nuevaCripto->id-$nuevaCripto->nombre.$extension");

    return $response->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    $id = $args['id'];
    $buscada = CriptoMoneda::where('id', $id)->get();
    $payload = json_encode($buscada);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {    
    $tipo = $args['tipo'];
    if($tipo=='todos')
    {
        $lista = CriptoMoneda::all();
    }
    else
    {
        $lista = CriptoMoneda::where('nacionalidad', $tipo)->get();
    }

    $payload = json_encode(array("listaCripto" => $lista));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args)
  {
    /* $parametros = $request->getParsedBody();
    
    $IdAbuscar = $args['id'];

    $precio = $parametros['precio'];
    $nombre = $parametros['nombre'];
    $tipo = $parametros ['tipo'];
    $imagen = $_FILES['imagen'];

    $hortalizaAModificar = Hortaliza::where ('id', '=', $IdAbuscar)->first();

    if($hortalizaAModificar!==null)
    {
        $hortalizaAModificar->precio = $precio;
        $hortalizaAModificar->nombre = $nombre;
        $hortalizaAModificar->tipo = $tipo;

        $hortalizaAModificar->save();

        $extension = pathinfo($imagen['name'],PATHINFO_EXTENSION);
        $nombre_fichero = "../app/Fotos/Hortalizas/$hortalizaAModificar->id-$hortalizaAModificar->nombre.$extension";
        
        if (file_exists($nombre_fichero)) 
        {
          $nombre_fichero = "../app/Fotos/BackUp/$hortalizaAModificar->id-$hortalizaAModificar->nombre.$extension";
          move_uploaded_file($imagen['tmp_name'],$nombre_fichero);
        }
        else
        {
          move_uploaded_file($imagen['tmp_name'],$nombre_fichero);
        }

        $payload = json_encode(array("mensaje" => "Hortaliza modificada con exito"));
    }
    else
    {
        $payload = json_encode(array("mensaje" => "Hortaliza no encontrada"));
    } */
    $payload = json_encode(array("mensaje" => "Cripto no encontrada"));

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
    /* $IdaBuscar = $args['id'];
    // Buscamos el usuario
    $usuario = Hortaliza::find($IdaBuscar);
    // Borramos
    $usuario->delete(); */

    //$payload = json_encode(array("mensaje" => "Hortaliza borrada con exito"));
    $payload = json_encode(array("mensaje" => "Cripto no encontrada"));

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
  
    $todos = CriptoMoneda::all();
    $total=CriptoMoneda::all()->count();
    //$venta = Venta::find(1);

    $pdf->Cell(30,10,'id',1,0,'C',0);
    $pdf->Cell(30,10,'precio',1,0,'C',0);
    $pdf->Cell(30,10,'nombre',1,0,'C',0);
    $pdf->Cell(30,10,'nacionalidad',1,1,'C',0);
    
    if($total >1)
    {
      foreach($todos as $unoSolo)
      {
        $pdf->Cell(30,10,$unoSolo->id,1,0,'C',0);
        $pdf->Cell(30,10,$unoSolo->precio,1,0,'C',0);
        $pdf->Cell(30,10,$unoSolo->nombre,1,0,'C',0);
        $pdf->Cell(30,10,$unoSolo->nacionalidad,1,1,'C',0);
      }
    }
    else
    {
      $pdf->Cell(30,10,$todos->id,1,0,'C',0);
      $pdf->Cell(30,10,$todos->precio,1,0,'C',0);
      $pdf->Cell(30,10,$todos->nombre,1,0,'C',0);
      $pdf->Cell(30,10,$todos->nacionalidad,1,1,'C',0);
    }

    $pdf->Output();

    return $response;
  }

  public function GenerarCSV ($request, $response, $args)
  {
    $archivo = fopen("../archivosGenerados/CriptoMonedas.csv",'w');

    $todos = CriptoMoneda::all();

    if($archivo)
    {
      foreach($todos as $unoSolo)
      {
        $datos = $unoSolo ->id .",". $unoSolo ->precio . "," . $unoSolo ->nombre . "," . $unoSolo ->nacionalidad ."\n";
        
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