<?php
/**
 * Arcon Push Service
 * Chama a API REST do Supabase para atualizar empresa/avisos no Arcon
 * Uso interno — chamado por outros scripts PHP do Gestor
 */

class ArconPush {

    private string $url;
    private string $key;
    private bool   $enabled;

    public function __construct() {
        $this->url     = rtrim(getenv('SUPABASE_URL') ?: '', '/');
        $this->key     = getenv('SUPABASE_SERVICE_KEY') ?: '';
        $this->enabled = !empty($this->url) && !empty($this->key);
    }

    public function isEnabled(): bool {
        return $this->enabled;
    }

    // ─── Empresas ────────────────────────────────────────────────

    /**
     * Atualiza a empresa no Supabase pelo gestor_cliente_id
     * Usado ao ativar/suspender/cancelar assinatura
     */
    public function atualizarEmpresa(int $gestorClienteId, array $dados): array {
        if (!$this->enabled) return ['ok' => false, 'msg' => 'Supabase não configurado'];

        $payload = array_filter([
            'assinatura_status'          => $dados['assinatura_status'] ?? null,
            'plano'                      => $dados['plano'] ?? null,
            'gestor_plano_contratado_id' => $dados['gestor_plano_contratado_id'] ?? null,
            'assinatura_cliente'         => $dados['assinatura_cliente'] ?? null,
            'assinatura_atualizada_em'   => date('c'),
        ], fn($v) => $v !== null);

        return $this->request(
            'PATCH',
            "/rest/v1/empresas?gestor_cliente_id=eq.{$gestorClienteId}",
            $payload
        );
    }

    /**
     * Vincula uma empresa no Supabase ao cliente do Gestor
     * Usa o e-mail para localizar a empresa e grava o gestor_cliente_id
     */
    public function vincularEmpresaPorEmail(string $email, int $gestorClienteId, int $gestorPlanoId, string $plano, string $status): array {
        if (!$this->enabled) return ['ok' => false, 'msg' => 'Supabase não configurado'];

        return $this->request(
            'PATCH',
            "/rest/v1/empresas?email=eq." . urlencode($email),
            [
                'gestor_cliente_id'          => $gestorClienteId,
                'gestor_plano_contratado_id' => $gestorPlanoId,
                'plano'                      => $plano,
                'assinatura_status'          => $status,
                'assinatura_atualizada_em'   => date('c'),
            ]
        );
    }

    // ─── Avisos ──────────────────────────────────────────────────

    public function criarAviso(string $titulo, string $mensagem, string $tipo = 'info'): array {
        if (!$this->enabled) return ['ok' => false, 'msg' => 'Supabase não configurado'];

        return $this->request('POST', '/rest/v1/avisos_sistema', [
            'titulo'   => $titulo,
            'mensagem' => $mensagem,
            'tipo'     => $tipo,
            'ativo'    => true,
        ]);
    }

    public function desativarAviso(int $avisoSupabaseId): array {
        if (!$this->enabled) return ['ok' => false, 'msg' => 'Supabase não configurado'];

        return $this->request('PATCH', "/rest/v1/avisos_sistema?id=eq.{$avisoSupabaseId}", [
            'ativo' => false,
        ]);
    }

    public function reativarAviso(int $avisoSupabaseId): array {
        if (!$this->enabled) return ['ok' => false, 'msg' => 'Supabase não configurado'];
        return $this->request('PATCH', "/rest/v1/avisos_sistema?id=eq.{$avisoSupabaseId}", ['ativo' => true]);
    }

    public function excluirAviso(int $avisoSupabaseId): array {
        if (!$this->enabled) return ['ok' => false, 'msg' => 'Supabase não configurado'];

        return $this->request('DELETE', "/rest/v1/avisos_sistema?id=eq.{$avisoSupabaseId}", []);
    }

    public function buscarEmpresaPorEmail(string $email): array {
        return $this->request('GET', '/rest/v1/empresas?email=eq.' . urlencode($email) . '&select=id,nome_fantasia,plano,assinatura_status,gestor_cliente_id', []);
    }

    // ─── Profiles ────────────────────────────────────────────────

    /**
     * Bloqueia/desbloqueia todos os profiles de uma empresa no Arcon
     */
    public function atualizarAtivoEmpresa(int $gestorClienteId, bool $ativo): array {
        if (!$this->enabled) return ['ok' => false, 'msg' => 'Supabase não configurado'];

        // Primeiro busca o id da empresa
        $emp = $this->request('GET', "/rest/v1/empresas?gestor_cliente_id=eq.{$gestorClienteId}&select=id", []);
        if (!$emp['ok'] || empty($emp['data'][0]['id'])) {
            return ['ok' => false, 'msg' => 'Empresa não encontrada no Supabase'];
        }

        $empresaId = $emp['data'][0]['id'];
        return $this->request('PATCH', "/rest/v1/profiles?empresa_id=eq.{$empresaId}", [
            'ativo' => $ativo,
        ]);
    }

    // ─── HTTP helper ─────────────────────────────────────────────

    private function request(string $method, string $path, array $body): array {
        $headers = [
            'apikey: '        . $this->key,
            'Authorization: Bearer ' . $this->key,
            'Content-Type: application/json',
            'Prefer: return=representation',
        ];

        $ch = curl_init($this->url . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CUSTOMREQUEST  => $method,
        ]);

        if ($method !== 'GET' && $method !== 'DELETE') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) return ['ok' => false, 'msg' => "cURL: {$curlErr}"];

        $data = json_decode($response, true);
        $ok   = $httpCode >= 200 && $httpCode < 300;

        return [
            'ok'   => $ok,
            'code' => $httpCode,
            'data' => $data,
            'msg'  => $ok ? 'ok' : ($data['message'] ?? "HTTP {$httpCode}"),
        ];
    }
}
