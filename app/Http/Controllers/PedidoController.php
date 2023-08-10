<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\CarritoProducto;
use Illuminate\Http\Request;
use App\Providers\StripeService;
//use Stripe\Stripe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PedidoController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->middleware('can:pedidos.pedidos')->only('allPedidos');
        $this->middleware('can:pedidos.detalle')->only('detallePedido');
        $this->middleware('can:pedidos.realizar')->only('realizarPedido');
        $this->stripeService = $stripeService;
    }

    public function realizarPedido(Request $request)
    {
        // Valida la solicitud
        $request->validate([
            'direccion' => 'required|string',
            'celular' => 'required|string',
            'token' => 'required|string', // Token de Stripe para el pago
        ]);

        $user = auth()->user();
        $carrito = $user->carritos()->where('estado', 'Activo')->firstOrFail();

        // Obtén los productos en el carrito
        $productos = $carrito->productos;

        // Calcula el total del pedido
        $total = 0;
        foreach ($productos as $producto) {
            $total += $producto->precio * $producto->pivot->cantidad;
        }

        // Realiza el pago con Stripe
        Stripe::setApiKey(config('services.stripe.secret'));
        $charge = \Stripe\Charge::create([
            'amount' => $total * 100, // Stripe trabaja con cantidades en centavos
            'currency' => 'usd', // Cambia a tu moneda
            'source' => $request->token,
            'description' => 'Pago de pedido',
        ]);

        // Crea el pedido en la base de datos
        $pedido = Pedido::create([
            'user_id' => $user->id,
            'direccion' => $request->direccion,
            'celular' => $request->celular,
            'total' => $total,
            'estado' => 'Pendiente', // Cambia según tus estados de pedido
        ]);

        // Crea los detalles del pedido
        foreach ($productos as $producto) {
            $pedido->detalles()->create([
                'producto_id' => $producto->id,
                'cantidad' => $producto->pivot->cantidad,
                'precio_unitario' => $producto->precio,
            ]);
        }

        // Vacía el carrito
        foreach ($productos as $producto) {
            // Aumenta el stock de los productos eliminados del carrito
            $producto->stock += $producto->pivot->cantidad;
            $producto->save();
        }
        $carrito->productos()->detach();

        return response()->json(['message' => 'Pedido realizado con éxito']);
    }

    public function verDetallePedido(Pedido $pedido)
    {
        // Asegúrate de que el usuario autenticado sea el dueño del pedido
        $this->authorize('view', $pedido);

        try {
            $detallePedido = $pedido->detalles;
            if ($detallePedido->isEmpty()) {
                throw new ModelNotFoundException();
            }

            return response()->json(['detalle_pedido' => $detallePedido]);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'No se encontró ningún detalle para este pedido'], 404);
        }
    }

    public function verDetallesPedidos()
    {
        // Obtén los pedidos del usuario autenticado
        $user = auth()->user();
        $pedidos = $user->pedidos;

        if ($pedidos->isEmpty()) {
            return response()->json(['message' => 'No has realizado ningún pedido'], 404);
        }

        return response()->json([
            'Pedidos' => $pedidos,
        ]);
    }
}
