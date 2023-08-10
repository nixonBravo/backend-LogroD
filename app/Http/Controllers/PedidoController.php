<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use App\Services\StripeService;
/* use Stripe\Stripe;
use Stripe\Charge; */

class PedidoController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->middleware('can:pedidos.comprar')->only('realizarPedido');
        $this->middleware('can:pedidos.ver')->only('verPedidos');
        $this->stripeService = $stripeService;
    }

    public function realizarPedido(Request $request)
    {
        try {
            $request->validate([
                'direccion' => 'required|string',
                'celular' => 'required|string',
            ]);

            $user = auth()->user();
            $carrito = $user->carritos()->where('estado', 'Activo')->firstOrFail();

            $productos = $carrito->productos;

            $total = 0;
            foreach ($productos as $producto) {
                $total += $producto->precio * $producto->pivot->cantidad;
            }

            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $token = 'tok_visa';
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $charge = $stripe->charges->create([
                'amount' => $total * 100,
                'currency' => 'usd',
                'source' => $token,
                'description' => $request->description,
            ]);

            $pedido = Pedido::create([
                'user_id' => $user->id,
                'direccion' => $request->direccion,
                'celular' => $request->celular,
                'total' => $total,
                'estado' => 'Pendiente',
            ]);

            foreach ($productos as $producto) {
                $pedido->detalles()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $producto->pivot->cantidad,
                    'precio_unitario' => $producto->precio,
                    'total' => $total
                ]);
            }

            $carrito->productos()->detach();

            return response()->json([
                'message' => 'Pedido realizado con éxito',
                'Status' => $charge->status,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'No se pudo realizar la compra',
                //'erros' => $th->getMessage()
            ]);
        }
    }

    public function verPedidos()
    {
        try {
            $user = auth()->user();
            $pedidos = $user->pedidos;

            if ($pedidos->isEmpty()) {
                return response()->json([
                    'message' => 'No has realizado ningún pedido'
                ], 404);
            }

            return response()->json([
                'Pedidos' => $pedidos,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'No se pueden ver Detalles de Pedidos',
            ], 500);
        }
    }
}
