<?php

namespace App\OpenApi;

/**
 * @OA\Info(
 *     version="0.2.0",
 *     title="Snack Eclat API",
 *     description="API untuk data snack, transaksi, analisis ECLAT, dan laporan pola penjualan."
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
final class Swagger {}
