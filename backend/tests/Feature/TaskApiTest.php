<?php

namespace Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;

class TaskApiTest extends TestCase
{
    private Client $http;

    /**
     * Configura o cliente HTTP antes de cada teste.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->http = new Client([
            // O 'base_uri' aponta para o nome do serviço do Nginx no Docker Compose.
            'base_uri' => 'http://webserver',
            // Pede ao Guzzle para lançar exceções em erros HTTP (4xx e 5xx).
            'http_errors' => true,
            'timeout' => 2,
        ]);
    }

    /**
     * Testa se a API falha corretamente ao tentar criar uma tarefa sem título.
     */
    public function test_should_fail_when_creating_task_without_title(): void
    {
        try {
            $this->http->post('/task', [
                'json' => [
                    'description' => 'Tarefa sem título para teste.'
                ]
            ]);

            // Se a requisição não falhar, o teste deve ser forçado a falhar.
            $this->fail('A requisição deveria ter falhado com status 422, mas foi bem-sucedida.');

        } catch (ClientException $e) {
            // 1. Verifica se o código de status da resposta é 422 (Unprocessable Entity).
            $this->assertEquals(422, $e->getResponse()->getStatusCode());

            // 2. Verifica se o corpo da resposta contém a mensagem de erro de validação para o campo 'title'.
            $responseBody = json_decode($e->getResponse()->getBody()->getContents(), true);

            $this->assertArrayHasKey('errors', $responseBody, 'O corpo da resposta deve conter a chave "errors".');
            $this->assertArrayHasKey('title', $responseBody['errors'], 'O objeto "errors" deve conter uma mensagem para o campo "title".');
        }
    }
}
