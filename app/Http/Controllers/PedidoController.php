<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\Pedido;
use Illuminate\Http\Request;
use App\Providers\StripeService;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{
    /* public function stripePost(Request $request)
    {
        try {
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            $res = $stripe->tokens->create([
                'card' => [
                    'number' => $request->number, //'4242424242424242',
                    'exp_month' => $request->exp_month, //8,
                    'exp_year' => $request->exp_year, //2024,
                    'cvc' => $request->cvc //'314',
                ],
            ]);

            Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $response = $stripe->charges->create([
                'amount' => $request->amount,//2000,
                'currency' => 'usd',
                'source' => $request->id,//'tok_visa',
                'description' => $request->description,
            ]);
            return response()->json([
                'Status' => $response->status,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error',
            ], 500);
        }
    } */

    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function realizarPedido(Request $request)
    {
        $user = Auth::user();
        $carrito = $user->carrito;

        $pedido = new Pedido([
            'user_id' => $user->id,
            'direccion' => $request->direccion,
            'celular' => $request->celular,
            'estado' => 'Pendiente',
            'total' => $carrito->calcularTotal(),
        ]);
        $pedido->save();

        foreach ($carrito->productosEnCarrito as $productoEnCarrito) {
            $detalle = new DetallePedido([
                'pedido_id' => $pedido->id,
                'producto_id' => $productoEnCarrito->producto_id,
                'cantidad' => $productoEnCarrito->cantidad,
                'precio_unitario' => $productoEnCarrito->producto->precio,
            ]);
            $detalle->save();
        }

        $paymentIntent = $this->stripeService->createPaymentIntent($pedido->total);

        if ($paymentIntent->status === 'Succeeded') {
            $pedido->estado = 'Completado';
            $pedido->save();
        }

        $carrito->productosEnCarrito()->delete();

        return response()->json([
            'message' => 'Pedido realizado con éxito'
        ], 200);
    }

    public function detallePedido($id)
    {

        $user = Auth::user();
        $pedido = Pedido::where('user_id', $user->id)->find($id);

        if (!$pedido) {
            return response()->json([
                'message' => 'No tienes ningún pedido con ese ID'
            ], 404);
        }

        return response()->json([
            'Pedido' => $pedido
        ]);
    }

    public function allPedidos()
    {
        $user = Auth::user();
        $pedidos = Pedido::where('user_id', $user->id)->get();

        if ($pedidos->isEmpty()) {
            return response()->json([
                'message' => 'No tienes ningún pedido'
            ]);
        }

        return response()->json([
            'Pedidos' => $pedidos
        ]);
    }
}
