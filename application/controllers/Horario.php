<?php

defined('BASEPATH') OR exit('No direct script access allowed');
// Proteção do CodeIgniter para impedir acesso direto ao arquivo via URL

class Horario extends CI_Controller {

    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    1  - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    2  - Conteúdo passado nulo ou vazio
    3  - Conteúdo zerado
    4  - Conteúdo não inteiro
    5  - Conteúdo não é um texto
    6  - Data em formato inválido
    7  - Hora em formato inválido
    12 - Na atualização, pelo menos um atributo deve ser passado
    13 - Hora Final menor que a Hora Inicial
    14 - Data Final menor que a Data Inicial.
    99 - Parâmetros passados do front não correspondem ao método
    */

    // Atributos privados da classe
    private $codigo;      // Armazena o código do horário
    private $descricao;   // Armazena a descrição do horário
    private $horaInicial; // Armazena a hora inicial do horário
    private $horaFinal;   // Armazena a hora final do horário
    private $estatus;     // Armazena o status do horário

    // Getters dos atributos
    public function getCodigo()
    {
        return $this->codigo; // Retorna o código
    }

    public function getDescricao()
    {
        return $this->descricao; // Retorna a descrição
    }

    public function getHoraInicial()
    {
        return $this->horaInicial; // Retorna a hora inicial
    }

    public function getHoraFinal()
    {
        return $this->horaFinal; // Retorna a hora final
    }

    public function getEstatus()
    {
        return $this->estatus; // Retorna o estatus
    }

    // Setters dos atributos
    public function setCodigo($codigoFront)
    {
        $this->codigo = $codigoFront; // Define o código com o valor vindo do frontend
    }

    public function setDescricao($descricaoFront)
    {
        $this->descricao = $descricaoFront; // Define a descrição com o valor vindo do frontend
    }

    public function setHoraInicial($horaInicialFront)
    {
        $this->horaInicial = $horaInicialFront; // Define a hora inicial com o valor vindo do frontend
    }

    public function setHoraFinal($horaFinalFront)
    {
        $this->horaFinal = $horaFinalFront; // Define a hora final com o valor vindo do frontend
    }

    public function setEstatus($estatusFront)
    {
        $this->tipoUsuario = $estatusFront; // Define o estatus com o valor vindo do frontend
    }

    public function inserir() {
        $this->load->helper('geral_helper');

        // Atributos para controlar o status de nosso método
        $erros   = [];    // Array que armazena os erros encontrados
        $sucesso = false; // Variável que indica se a operação foi bem-sucedida

        try {

            $json = file_get_contents('php://input'); // Lê o corpo da requisição enviado pelo frontend
            $resultado = json_decode($json);               // Converte o JSON recebido em um objeto PHP

            $lista = [ // Define os campos esperados vindos do frontend
                "descricao"   => '0',
                "horaInicial" => '0',
                "horaFinal"   => '0'
            ];

                // Linha 94 - Verifica se os parâmetros recebidos são válidos (retorno != 1 indica erro)
            if (verificarParametros($resultado, $lista) != 1) {

                // Linha 95 - Comentário: valida dados vindos corretamente do frontend via Helper
                // Linha 96 - Adiciona erro no array com código 99 e mensagem de campos inválidos
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];

            // Linha 97 - Caso os parâmetros sejam válidos, entra no else
            } else {
                // Valida o campo 'descricao' esperando tipo string e obrigatório (true)
                $retornoDescricao = validarDados($resultado->descricao, 'string', true);

                // Valida o campo 'horaInicial' esperando tipo hora e obrigatório (true)
                $retornoHoraInicial = validarDados($resultado->horaInicial, 'hora', true);

                // Linha 101 - Valida o campo 'horaFinal' esperando tipo hora e obrigatório (true)
                $retornoHoraFinal = validarDados($resultado->horaFinal, 'hora', true);

                // Linha 102-103 - Compara horaInicial com horaFinal para garantir ordem cronológica
                $retornoComparacaoHoras = compararDataHora(
                    $resultado->horaInicial,
                    $resultado->horaFinal,
                    'hora'
                );

                // Linha 105 - Verifica se houve erro na validação da descrição (codigoHelper != 0 = erro)
                if ($retornoDescricao['codigoHelper'] != 0) {

                    // Linha 106-108 - Adiciona erro com código, campo 'Descrição' e mensagem do Helper
                    $erros[] = [
                        'codigo' => $retornoDescricao['codigoHelper'],
                        'campo'  => 'Descrição',
                        'msg'    => $retornoDescricao['msg']
                    ];
                }   

                // Linha 111 - Verifica se houve erro na validação da hora inicial
                if ($retornoHoraInicial['codigoHelper'] != 0) {

                    // Linha 112-114 - Adiciona erro com código, campo 'Hora Inicial' e mensagem
                    $erros[] = [
                        'codigo' => $retornoHoraInicial['codigoHelper'],
                        'campo'  => 'Hora Inicial',
                        'msg'    => $retornoHoraInicial['msg']
                    ];

                // Linha 115 - Fecha o if de validação da hora inicial
                }

                // Linha 117 - Verifica se houve erro na validação da hora final
                if ($retornoHoraFinal['codigoHelper'] != 0) {

                    // Linha 118-120 - Adiciona erro com código, campo 'Hora Final' e mensagem
                    $erros[] = [
                        'codigo' => $retornoHoraFinal['codigoHelper'],
                        'campo'  => 'Hora Final',
                        'msg'    => $retornoHoraFinal['msg']
                    ];
                }

                // Linha 123 - Comentário: valida se a hora inicial é maior que a hora final
                // Linha 124 - Verifica se houve erro na comparação entre as horas
                if ($retornoComparacaoHoras['codigoHelper'] != 0) {

                    // Linha 125-127 - Adiciona erro indicando conflito entre Hora Inicial e Hora Final
                    $erros[] = [
                        'codigo' => $retornoComparacaoHoras['codigoHelper'],
                        'campo'  => 'Hora Inicial e Hora Final',
                        'msg'    => $retornoComparacaoHoras['msg']
                    ];
                }

                //condição adicionada para que os horarios de inicio e final não sejam iguais 
                if ($resultado->horaInicial === $resultado->horaFinal) {
                    $erros[] = [
                        'codigo' => 13, 
                        'campo'  => 'Hora Inicial e Hora Final',
                        'msg'    => 'Os horários de início e fim não podem ser iguais.'
                    ];
                }
                // Linha 130 - Comentário: se não encontrar erros, prossegue com o cadastro
                // Linha 131 - Verifica se o array de erros está vazio (sem erros)
                if (empty($erros)) {

                    // Linha 132 - Define a descrição no objeto atual via setter
                    $this->setDescricao($resultado->descricao);

                    // Linha 133 - Define a hora inicial no objeto atual via setter
                    $this->setHoraInicial($resultado->horaInicial);

                    // Linha 134 - Define a hora final no objeto atual via setter
                    $this->setHoraFinal($resultado->horaFinal);

                    // Linha 136 - Carrega o model 'M_horario' para acesso ao banco de dados
                    $this->load->model('M_horario');

                    // Linha 137-141 - Chama o método inserir do model passando os dados via getters
                    $resBanco = $this->M_horario->inserir(
                        $this->getDescricao(),    // Linha 138 - Passa a descrição
                        $this->getHoraInicial(),  // Linha 139 - Passa a hora inicial
                        $this->getHoraFinal()     // Linha 140 - Passa a hora final
                    );

                    // Linha 143 - Verifica se o banco retornou código 1 (sucesso na inserção)
                    if ($resBanco['codigo'] == 1) {

                        // Linha 144 - Define sucesso como verdadeiro
                        $sucesso = true;

                    // Linha 145 - Caso contrário (erro no banco)
                    } else {

                        // Linha 146 - Comentário: captura o erro retornado pelo banco
                        // Linha 147-150 - Adiciona erro com código e mensagem vindos do banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg'    => $resBanco['msg']
                        ];
                    // Linha 151 - Fecha o else do erro do banco
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        // Linha 158 - Comentário: monta o retorno único da função
        // Linha 159 - Se a operação foi bem-sucedida
        if ($sucesso == true) {
            // Linha 160-161 - Retorno com sucesso, código e mensagem do banco
            $retorno = [
                'sucesso' => $sucesso,
                'codigo'  => $resBanco['codigo'],
                'msg'     => $resBanco['msg']
            ];

        // Linha 162 - Caso tenha ocorrido algum erro
        } else {
            // Linha 163 - Retorno com sucesso false e array de erros acumulados
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        echo json_encode($retorno);
    }

    public function consultar() {
        $this->load->helper('geral_helper');
        // Linha 171 - Comentário: atributos para controlar o status do método
        // Linha 172 - Inicializa o array de erros vazio
        $erros = [];

        // Linha 173 - Inicializa o status de sucesso como false
        $sucesso = false;

        // Linha 175 - Inicia o bloco try para capturar exceções
        try {

            // Linha 177 - Lê o corpo da requisição HTTP (JSON enviado pelo frontend)
            $json = file_get_contents('php://input');

            // Linha 178 - Decodifica o JSON recebido em objeto PHP
            $resultado = json_decode($json);

            // Linha 179-184 - Define a lista de campos esperados com seus valores padrão '0'
            $lista = [
                "codigo"     => '0',  // Linha 180 - Campo código esperado
                "descricao"  => '0',  // Linha 181 - Campo descrição esperado
                "horaInicial"=> '0',  // Linha 182 - Campo hora inicial esperado
                "horaFinal"  => '0'   // Linha 183 - Campo hora final esperado
            ];

            // Linha 186 - Verifica se os parâmetros recebidos são válidos
            if (verificarParametros($resultado, $lista) != 1) {

                // Linha 187 - Comentário: valida dados vindos corretamente do frontend
                // Linha 188 - Adiciona erro com código 99 indicando campos inválidos/ausentes
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];

            // Linha 189 - Caso os parâmetros sejam válidos, entra no else
            } else {

                // Linha 190 - Comentário: valida campos quanto ao tipo de dado e tamanho

                // Linha 191 - Valida o campo 'codigo' esperando tipo inteiro (sem obrigatoriedade)
                $retornoCodigo = validarDadosConsulta($resultado->codigo, 'int');

                // Linha 192 - Valida o campo 'descricao' esperando tipo string
                $retornoDescricao = validarDadosConsulta($resultado->descricao, 'string');

                // Linha 193 - Valida o campo 'horaInicial' esperando tipo hora
                $retornoHoraInicial = validarDadosConsulta($resultado->horaInicial, 'hora');

                // Linha 194 - Valida o campo 'horaFinal' esperando tipo hora
                $retornoHoraFinal = validarDadosConsulta($resultado->horaFinal, 'hora');

                // Linha 195-196 - Compara horaInicial com horaFinal para verificar consistência
                $retornoComparacaoHoras = compararDataHora(
                    $resultado->horaInicial,
                    $resultado->horaFinal,
                    'hora'
                );

                // Linha 198 - Verifica se houve erro na validação do código
                if ($retornoCodigo['codigoHelper'] != 0) {

                    // Linha 199-201 - Adiciona erro com código, campo 'Codigo' e mensagem
                    $erros[] = [
                        'codigo' => $retornoCodigo['codigoHelper'],
                        'campo'  => 'Codigo',
                        'msg'    => $retornoCodigo['msg']
                    ];

                // Linha 202 - Fecha o if de validação do código
                }

                // Linha 204 - Verifica se houve erro na validação da descrição
                if ($retornoDescricao['codigoHelper'] != 0) {

                    // Linha 205-207 - Adiciona erro com código, campo 'Descrição' e mensagem
                    $erros[] = [
                        'codigo' => $retornoDescricao['codigoHelper'],
                        'campo'  => 'Descrição',
                        'msg'    => $retornoDescricao['msg']
                    ];

                // Linha 208 - Fecha o if de validação da descrição
                }

                // Linha 210 - Verifica se houve erro na validação da hora inicial
                if ($retornoHoraInicial['codigoHelper'] != 0) {

                    // Linha 211-213 - Adiciona erro com código, campo 'Hora Inicial' e mensagem
                    $erros[] = [
                        'codigo' => $retornoHoraInicial['codigoHelper'],
                        'campo'  => 'Hora Inicial',
                        'msg'    => $retornoHoraInicial['msg']
                    ];

                // Linha 214 - Fecha o if de validação da hora inicial
                }

                // Linha 216 - Verifica se houve erro na validação da hora final
                if ($retornoHoraFinal['codigoHelper'] != 0) {

                    // Linha 217-219 - Adiciona erro com código, campo 'Hora Final' e mensagem
                    $erros[] = [
                        'codigo' => $retornoHoraFinal['codigoHelper'],
                        'campo'  => 'Hora Final',
                        'msg'    => $retornoHoraFinal['msg']
                    ];

                // Linha 220 - Fecha o if de validação da hora final
                }

                // Linha 222 - Comentário: valida se a hora inicial é maior que a hora final
                // Linha 223 - Verifica se houve erro na comparação entre as horas
                if ($retornoComparacaoHoras['codigoHelper'] != 0) {

                    // Linha 224-226 - Adiciona erro indicando conflito entre Hora Inicial e Hora Final
                    $erros[] = [
                        'codigo' => $retornoComparacaoHoras['codigoHelper'],
                        'campo'  => 'Hora Inicial e Hora Final',
                        'msg'    => $retornoComparacaoHoras['msg']
                    ];

                // Linha 227 - Fecha o if de comparação de horas
                }

                // Linha 229 - Comentário: se não encontrar erros, prossegue com a consulta
                // Linha 230 - Verifica se o array de erros está vazio
                if (empty($erros)) {

                    // Linha 231 - Define o código no objeto atual via setter
                    $this->setCodigo($resultado->codigo);
                    // Linha 232 - Define a descrição no objeto via setter
                    $this->setDescricao($resultado->descricao);

                    // Linha 233 - Define a hora inicial no objeto via setter
                    $this->setHoraInicial($resultado->horaInicial);

                    // Linha 234 - Define a hora final no objeto via setter
                    $this->setHoraFinal($resultado->horaFinal);

                    // Linha 236 - Carrega o model 'M_horario' para acesso ao banco
                    $this->load->model('M_horario');

                    // Linha 237-240 - Chama o método consultar do model passando os 4 filtros via getters
                    $resBanco = $this->M_horario->consultar(
                        $this->getCodigo(),      // Linha 237 - Passa o código como filtro
                        $this->getDescricao(),   // Linha 238 - Passa a descrição como filtro
                        $this->getHoraInicial(), // Linha 239 - Passa a hora inicial como filtro
                        $this->getHoraFinal()    // Linha 240 - Passa a hora final como filtro
                    );

                    // Linha 242 - Verifica se o banco retornou código 1 (consulta bem-sucedida)
                    if ($resBanco['codigo'] == 1) {

                        // Linha 243 - Define sucesso como verdadeiro
                        $sucesso = true;

                    // Linha 244 - Caso contrário (erro no banco)
                    } else {
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg'    => $resBanco['msg']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        if ($sucesso == true) {
            // Linha 259-261 - Retorno com sucesso, código, mensagem E os dados consultados
            $retorno = [
                'sucesso' => $sucesso,
                'codigo'  => $resBanco['codigo'],
                'msg'     => $resBanco['msg'],
                'dados'   => $resBanco['dados']  // diferencial: retorna os registros encontrados
            ];

        // Linha 262 - Caso tenha ocorrido algum erro
        } else {
            // Linha 263 - Retorno com sucesso false e array de erros
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        // Linha 266 - Comentário: transforma o array em JSON
        // Linha 267 - Envia a resposta JSON para o cliente
        echo json_encode($retorno);
    }

    // Linha 270 - Declara a função pública 'alterar'
    public function alterar() {
        $this->load->helper('geral_helper');
        $erros = [];
        // Linha 273 - Inicializa o status de sucesso como false
        $sucesso = false;
        // Linha 275 - Inicia o bloco try para capturar exceções
        try {
            // Linha 277 - Lê o corpo da requisição HTTP (JSON do frontend)
            $json = file_get_contents('php://input');

            // Linha 278 - Decodifica o JSON em objeto PHP
            $resultado = json_decode($json);

            // Linha 279-284 - Define a lista de campos esperados com valores padrão '0'
            $lista = [
                "codigo"      => '0', // Linha 280 - Campo código esperado
                "descricao"   => '0', // Linha 281 - Campo descrição esperado
                "horaInicial" => '0', // Linha 282 - Campo hora inicial esperado
                "horaFinal"   => '0'  // Linha 283 - Campo hora final esperado
            ];

            // Linha 286 - Verifica se os parâmetros recebidos são válidos
            if (verificarParametros($resultado, $lista) != 1) {
                // Linha 287 - Comentário: valida dados vindos corretamente do frontend
                // Linha 288 - Adiciona erro com código 99 indicando campos inválidos
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                // Linha 290 - Comentário: pelo menos um dos três parâmetros precisa ter dados
                // Linha 291-292 - Verifica se descrição, horaInicial E horaFinal estão todos vazios
                if (trim($resultado->descricao) == '' && trim($resultado->horaInicial) == '' &&
                    trim($resultado->horaFinal) == '') {
                    // Linha 293-294 - Erro código 12: nenhum campo de alteração foi enviado
                    $erros[] = [
                        'codigo' => 12,
                        'msg'    => 'Pelo menos um parâmetro precisa ser passado para atualização'
                    ];
                // Linha 295 - Se ao menos um campo foi preenchido, valida os dados
                } else {
                    // Linha 296 - Comentário: valida campos quanto ao tipo de dado e tamanho
                    // Linha 297 - Valida 'codigo' como inteiro e OBRIGATÓRIO (true) — chave da atualização
                    $retornoCodigo = validarDados($resultado->codigo, 'int', true);
                    // Linha 298 - Valida 'descricao' como string (opcional para alteração)
                    $retornoDescricao = validarDadosConsulta($resultado->descricao, 'string');
                    // Linha 299 - Valida 'horaInicial' como hora (opcional para alteração)
                    $retornoHoraInicial = validarDadosConsulta($resultado->horaInicial, 'hora');
                    // Linha 300 - Valida 'horaFinal' como hora (opcional para alteração)
                    $retornoHoraFinal = validarDadosConsulta($resultado->horaFinal, 'hora');
                    // Linha 301-302 - Compara horaInicial com horaFinal para garantir consistência
                    $retornoComparacaoHoras = compararDataHora(
                        $resultado->horaInicial,
                        $resultado->horaFinal,
                        'hora'
                    );

                    // Linha 304 - Verifica se houve erro na validação do código
                    if ($retornoCodigo['codigoHelper'] != 0) {

                        // Linha 305-307 - Adiciona erro com código, campo 'Codigo' e mensagem
                        $erros[] = [
                            'codigo' => $retornoCodigo['codigoHelper'],
                            'campo'  => 'Codigo',
                            'msg'    => $retornoCodigo['msg']
                        ];
                    }

                    // Linha 310 - Verifica se houve erro na validação da descrição
                    if ($retornoDescricao['codigoHelper'] != 0) {

                        // Linha 311-313 - Adiciona erro com código, campo 'Descrição' e mensagem
                        $erros[] = [
                            'codigo' => $retornoDescricao['codigoHelper'],
                            'campo'  => 'Descrição',
                            'msg'    => $retornoDescricao['msg']
                        ];
                    }

                    // Linha 316 - Verifica se houve erro na validação da hora inicial
                    if ($retornoHoraInicial['codigoHelper'] != 0) {

                        // Linha 317-319 - Adiciona erro com código, campo 'Hora Inicial' e mensagem
                        $erros[] = [
                            'codigo' => $retornoHoraInicial['codigoHelper'],
                            'campo'  => 'Hora Inicial',
                            'msg'    => $retornoHoraInicial['msg']
                        ];
                    }

                    // Linha 322 - Verifica se houve erro na validação da hora final
                    if ($retornoHoraFinal['codigoHelper'] != 0) {

                        // Linha 323-325 - Adiciona erro com código, campo 'Hora Final' e mensagem
                        $erros[] = [
                            'codigo' => $retornoHoraFinal['codigoHelper'],
                            'campo'  => 'Hora Final',
                            'msg'    => $retornoHoraFinal['msg']
                        ];
                    }

                    // Linha 328 - Comentário: valida se a hora inicial é maior que a hora final
                    // Linha 329 - Verifica se houve erro na comparação entre as horas
                    if ($retornoComparacaoHoras['codigoHelper'] != 0) {

                        // Linha 330-332 - Adiciona erro de conflito entre Hora Inicial e Hora Final
                        $erros[] = [
                            'codigo' => $retornoComparacaoHoras['codigoHelper'],
                            'campo'  => 'Hora Inicial e Hora Final',
                            'msg'    => $retornoComparacaoHoras['msg']
                        ];
                    }

                    // Linha 335 - Comentário: se não encontrar erros, prossegue com a alteração
                    // Linha 336 - Verifica se o array de erros está vazio
                    if (empty($erros)) {

                        // Linha 337 - Define o código no objeto via setter (identifica o registro)
                        $this->setCodigo($resultado->codigo);

                        // Linha 338 - Define a descrição no objeto via setter
                        $this->setDescricao($resultado->descricao);

                        // Linha 339 - Define a hora inicial no objeto via setter
                        $this->setHoraInicial($resultado->horaInicial);

                        // Linha 340 - Define a hora final no objeto via setter
                        $this->setHoraFinal($resultado->horaFinal);

                        // Linha 342 - Carrega o model 'M_horario' para acesso ao banco
                        $this->load->model('M_horario');

                        // Linha 343-347 - Chama o método alterar do model passando todos os dados
                        $resBanco = $this->M_horario->alterar(
                            $this->getCodigo(),      // Linha 343 - Código do registro a alterar
                            $this->getDescricao(),   // Linha 344 - Nova descrição
                            $this->getHoraInicial(), // Linha 345 - Nova hora inicial
                            $this->getHoraFinal()    // Linha 346 - Nova hora final
                        );

                        // Linha 348 - Verifica se o banco retornou código 1 (alteração bem-sucedida)
                        if ($resBanco['codigo'] == 1) {

                            // Linha 349 - Define sucesso como verdadeiro
                            $sucesso = true;

                        // Linha 350 - Caso contrário (erro no banco)
                        } else {

                            // Linha 351 - Comentário: captura erro do banco
                            // Linha 352-355 - Adiciona erro com código e mensagem do banco
                            $erros[] = [
                                'codigo' => $resBanco['codigo'],
                                'msg'    => $resBanco['msg']
                            ];
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }
        // Linha 365 - Verifica se a operação foi bem-sucedida
        if ($sucesso == true) {

            // Linha 366-368 - Retorno com sucesso, código e mensagem do banco
            $retorno = [
                'sucesso' => $sucesso,
                'codigo'  => $resBanco['codigo'],
                'msg'     => $resBanco['msg']
            ];
        // Linha 368 - Caso tenha ocorrido algum erro
        } else {

            // Linha 369 - Retorno com sucesso false e lista de erros
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        echo json_encode($retorno);
    }


    // Linha 376 - Declara a função pública 'desativar'
    public function desativar() {
        $this->load->helper('geral_helper');
        // Linha 377 - Comentário: atributos para controlar o status do método
        // Linha 378 - Inicializa o array de erros vazio
        $erros = [];

        // Linha 379 - Inicializa o status de sucesso como false
        $sucesso = false;

        // Linha 381 - Inicia o bloco try para capturar exceções
        try {

            // Linha 383 - Lê o corpo da requisição HTTP (JSON do frontend)
            $json = file_get_contents('php://input');

            // Linha 384 - Decodifica o JSON em objeto PHP
            $resultado = json_decode($json);

            // Linha 385-387 - Define a lista de campos esperados
            // *** Diferencial: só exige o campo 'codigo' — sem descrição ou horas ***
            $lista = [
                "codigo" => '0'  // Linha 386 - Apenas o código é necessário para desativar
            ];

            // Linha 389 - Verifica se os parâmetros recebidos são válidos
            if (verificarParametros($resultado, $lista) != 1) {

                // Linha 390 - Comentário: valida dados vindos corretamente do frontend
                // Linha 391 - Adiciona erro com código 99 indicando campos inválidos
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];

            // Linha 392 - Caso os parâmetros sejam válidos, entra no else
            } else {

                // Linha 393 - Comentário: valida código quanto ao tipo de dado e tamanho
                // Linha 394 - Valida 'codigo' como inteiro e OBRIGATÓRIO (true)
                $retornoCodigo = validarDados($resultado->codigo, 'int', true);

                // Linha 396 - Verifica se houve erro na validação do código
                if ($retornoCodigo['codigoHelper'] != 0) {

                    // Linha 397-399 - Adiciona erro com código, campo 'Codigo' e mensagem
                    $erros[] = [
                        'codigo' => $retornoCodigo['codigoHelper'],
                        'campo'  => 'Codigo',
                        'msg'    => $retornoCodigo['msg']
                    ];

                // Linha 400 - Fecha o if de validação do código
                }

                // Linha 402 - Comentário: se não encontrar erros, prossegue com a desativação
                // Linha 403 - Verifica se o array de erros está vazio
                if (empty($erros)) {

                    // Linha 404 - Define o código no objeto via setter (identifica o registro)
                    $this->setCodigo($resultado->codigo);

                    // Linha 406 - Carrega o model 'M_horario' para acesso ao banco
                    $this->load->model('M_horario');

                    // Linha 407 - Chama o método desativar do model passando apenas o código
                    // *** Diferencial: só passa getCodigo() — sem outros campos ***
                    $resBanco = $this->M_horario->desativar($this->getCodigo());

                    // Linha 409 - Verifica se o banco retornou código 1 (desativação bem-sucedida)
                    if ($resBanco['codigo'] == 1) {

                        // Linha 410 - Define sucesso como verdadeiro
                        $sucesso = true;

                    // Linha 411 - Caso contrário (erro no banco)
                    } else {

                        // Linha 412 - Comentário: captura erro do banco
                        // Linha 413-416 - Adiciona erro com código e mensagem do banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg'    => $resBanco['msg']
                        ];

                    // Linha 417 - Fecha o else do erro do banco
                    }

                // Linha 418 - Fecha o if (empty($erros))
                }

            // Linha 420 - Fecha o else principal
            }

        // Linha 421 - Captura qualquer exceção inesperada durante o processo
        } catch (Exception $e) {

            // Linha 422 - Adiciona erro com código 0 e mensagem da exceção
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];

        // Linha 423 - Fecha o catch
        }

        // Linha 425 - Comentário: monta o retorno único
        // Linha 426 - Verifica se a operação foi bem-sucedida
        if ($sucesso == true) {

            // Linha 427-428 - Retorno com sucesso, código e mensagem do banco
            $retorno = [
                'sucesso' => $sucesso,
                'codigo'  => $resBanco['codigo'],
                'msg'     => $resBanco['msg']
            ];

        // Linha 429 - Caso tenha ocorrido algum erro
        } else {

            // Linha 430 - Retorno com sucesso false e lista de erros
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];

        // Linha 431 - Fecha o else
        }

        // Linha 433 - Comentário: transforma o array em JSON
        // Linha 434 - Envia a resposta JSON para o cliente
        echo json_encode($retorno);

    // Linha 435 - Fecha a função desativar()
    }              
}
?>