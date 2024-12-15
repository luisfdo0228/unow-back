<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PositionController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/api/positions', name: 'api_positions', methods: ['GET'])]
    public function getPositions(): JsonResponse
    {
        try {
            // Realizar la solicitud a la API externa
            $response = $this->httpClient->request('GET', 'https://ibillboard.com/api/positions');

            // Verificar el estado de la respuesta
            if ($response->getStatusCode() !== 200) {
                return new JsonResponse(['error' => 'Failed to fetch positions'], 502);
            }

            // Obtener y procesar los datos
            $data = $response->toArray(); // Convierte la respuesta JSON a un array
            if (!isset($data['positions'])) {
                return new JsonResponse(['error' => 'Invalid response format'], 502);
            }

            return new JsonResponse($data['positions']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
