<?php

namespace Packages;

class CORS
{
    // --- CORS Handling ---
    // Define a origem permitida (seu frontend) - Não funciona
    public static string $allowedOrigin = 'http://localhost:3000';
    // Métodos HTTP permitidos
    public static string $allowedMethods = 'GET, POST, PUT, PATCH, DELETE, OPTIONS';
    // Cabeçalhos permitidos na requisição
    public static string $allowedHeaders = 'Content-Type, Authorization, X-Requested-With'; // Adicione outros cabeçalhos personalizados se usar

    public static function handle($request, &$response)
    {
        // Lida com a requisição OPTIONS (preflight)
        if ($request->getMethod() === 'OPTIONS') {
            $response = new \Symfony\Component\HttpFoundation\Response();
            $response->headers->set('Access-Control-Allow-Headers', self::$allowedHeaders);
            $response->headers->set('Access-Control-Allow-Methods', self::$allowedMethods);
            // $response->headers->set('Access-Control-Allow-Origin', self::$allowedOrigin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->setStatusCode(204);
            $response->send();
            // Termina o script após enviar a resposta do preflight
            exit();
        }

        $response->headers->set('Access-Control-Allow-Headers', self::$allowedHeaders);
        $response->headers->set('Access-Control-Allow-Methods', self::$allowedMethods);
        // $response->headers->set('Access-Control-Allow-Origin', self::$allowedOrigin);
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
    }
}