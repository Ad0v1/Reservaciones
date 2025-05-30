<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Pago;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\CardException;
use Carbon\Carbon;
use App\Http\Controllers\ReservaController;
Route::get('/reservas', [ReservaController::class, 'create'])->name('reservas.create');

class ReservaController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rname' => 'required|string|max:255',
            'rphone' => 'required|regex:/^9\d{8}$/',
            'rdate' => 'required|date|after:today',
            'rtime' => 'required',
            'rparty-size' => 'required|integer|min:1|max:250',
            'payment_method_id' => 'required'
        ]);

        return DB::transaction(function () use ($request) {
            try {
                // Calcular total
                $total = $this->calcularTotal($request);
                
                // Registrar/obtener usuario
                $user = User::firstOrCreate(
                    ['telefono' => $request->rphone],
                    [
                        'name' => $request->rname,
                        'email' => $request->email,
                        'password' => bcrypt(uniqid()) // Password temporal
                    ]
                );

                // Verificar disponibilidad
                $capacidad = Reserva::where('fecha_reserva', $request->rdate)
                    ->where('hora_reserva', $request->rtime)
                    ->sum('cantidad_personas');

                if (($capacidad + $request->input('rparty-size')) > 250) {
                    throw new \Exception('¡No hay disponibilidad para este horario!');
                }

                // Crear reserva
                $reserva = Reserva::create([
                    'user_id' => $user->id,
                    'fecha_reserva' => $request->rdate,
                    'hora_reserva' => $request->rtime,
                    'cantidad_personas' => $request->input('rparty-size'),
                    'total' => $total,
                    'estado' => 'pendiente',
                    'info_adicional' => $request->input('radd-info')
                ]);

                // Procesar pago con Stripe
                $payment = $user->charge(
                    $total * 100, // Monto en centavos
                    $request->payment_method_id,
                    [
                        'description' => 'Reserva #' . $reserva->id,
                        'metadata' => [
                            'reserva_id' => $reserva->id,
                            'user_id' => $user->id
                        ],
                        'currency' => config('cashier.currency'),
                    ]
                );

                // Registrar pago
                Pago::create([
                    'reserva_id' => $reserva->id,
                    'stripe_payment_id' => $payment->id,
                    'monto_pagado' => $total,
                    'metodo_pago' => 'stripe',
                    'estado' => $payment->status
                ]);

                // Actualizar estado de reserva
                $reserva->update(['estado' => 'confirmado']);

                return redirect()->back()
                    ->with('success', '¡Reserva confirmada! ID: ' . $reserva->id);

            } catch (CardException $e) {
                return redirect()->back()
                    ->withErrors(['error' => 'Error en tarjeta: ' . $e->getMessage()])
                    ->withInput();
                    
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withErrors(['error' => 'Error: ' . $e->getMessage()])
                    ->withInput();
            }
        });
    }

    private function generarHorarios()
    {
        $horarioApertura = Carbon::createFromTime(7, 0); // 07:00
        $horarioCierre = Carbon::createFromTime(23, 0);   // 23:00
        $intervalo = 30; // minutos
        $horarios = [];

        while ($horarioApertura <= $horarioCierre) {
            $horarios[] = $horarioApertura->format('H:i');
            $horarioApertura->addMinutes($intervalo);
        }

        return $horarios;
    }

    private function calcularTotal(Request $request)
    {
        $total = 0;
        $personas = $request->input('rparty-size');

        // Desayuno
        if ($request->filled('desayuno')) {
            $total += 9.00 * $personas;
        }

        // Almuerzo
        if ($request->filled('almuerzo_entrada')) {
            $total += 14.50 * $personas;
        }

        // Cena
        if ($request->filled('cena')) {
            $total += 16.50 * $personas;
        }

        return $total;
    }
}