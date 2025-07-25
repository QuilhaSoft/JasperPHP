### Plano de Refatoração: Injeção de Dependência do Objeto `Report`

**Raciocínio Atual:**

O problema central que estamos abordando é a forma como os elementos do relatório (como `TextField`, `Subreport`, etc.) acessam o contexto do relatório principal (parâmetros, campos, variáveis, dados da linha atual, etc.). Anteriormente, o objeto `Report` (ou uma parte dele) era passado como um parâmetro para o método `generate()` de cada elemento, e em alguns casos, o `$rowData` (dados da linha atual do banco de dados) também era passado separadamente.

Essa abordagem tem algumas desvantagens:
1.  **Acoplamento Forte:** Cada método `generate()` precisava saber sobre o objeto `Report`, tornando o código menos modular.
2.  **Duplicação de Parâmetros:** A passagem repetitiva de `$obj` e `$rowData` era redundante e propensa a erros.
3.  **Contexto Isolado para Sub-relatórios:** O `Subreport` criava uma nova instância de `Report` sem uma ligação clara com o relatório pai, dificultando a herança de contexto.

A solução que estamos implementando é a **injeção de dependência da instância do `Report`** no construtor de cada elemento. Isso significa que:
*   Cada elemento agora "conhece" o `Report` ao qual pertence desde o momento de sua criação.
*   O `Report` se torna o ponto central para acessar dados (como `$this->report->rowData`), parâmetros (`$this->report->arrayParameter`), e outras propriedades do relatório.
*   O método `generate()` dos elementos não precisa mais receber o objeto `Report` como parâmetro, simplificando suas assinaturas.

**Progresso até Agora:**

Concluímos a refatoração dos seguintes arquivos:

*   `src/elements/Element.php`:
    *   Adicionado `protected $report;` para armazenar a instância do `Report`.
    *   O construtor agora aceita `$report` e o passa para os filhos ao instanciá-los.
    *   O método `generate()` não aceita mais parâmetros.
*   `src/elements/Report.php`:
    *   O construtor agora aceita um `$parentReport` opcional (para sub-relatórios).
    *   No método `charge()`, a própria instância do `Report` (`$this`) é passada para o construtor dos elementos filhos.
    *   O método `generate()` não aceita mais parâmetros.
*   `src/elements/TextField.php`:
    *   O construtor agora aceita `$report` e o passa para `parent::__construct()`.
    *   O método `generate()` não aceita mais parâmetros.
    *   Todas as referências a `$obj` e `$rowData` foram substituídas por `$this->report` e `$this->report->rowData`, respectivamente.
*   `src/elements/Subreport.php`:
    *   O construtor agora aceita `$report` e o passa para `parent::__construct()`.
    *   O método `generate()` não aceita mais parâmetros.
    *   Todas as referências a `$obj` e `$row` foram substituídas por `$this->report` e `$this->report->rowData`.
    *   Ao instanciar o sub-relatório, a instância do `Report` pai (`$reportInstance`) é passada para o construtor do novo `Report`.

**Próximos Passos (Plano Detalhado):**

O objetivo é aplicar o mesmo padrão de refatoração a todos os outros elementos em `src/elements/` que possuem um método `generate()` e/ou precisam de acesso ao contexto do `Report`.

Para cada um dos arquivos listados abaixo, as seguintes modificações serão necessárias:

1.  **Modificar o Construtor:**
    *   Se o construtor existir, ele deve aceitar um segundo parâmetro `$report = null`.
    *   Este `$report` deve ser passado para o construtor da classe pai: `parent::__construct($ObjElement, $report);`.
    *   Exemplo:
        ```php
        class SomeElement extends Element
        {
            public function __construct($ObjElement, $report = null)
            {
                parent::__construct($ObjElement, $report);
            }
            // ...
        }
        ```

2.  **Refatorar o Método `generate()`:**
    *   Remover o parâmetro `$obj` (ou qualquer outro parâmetro que represente o `Report` ou `rowData`) da assinatura do método `generate()`.
    *   Dentro do método `generate()`, substituir todas as referências a:
        *   `$obj` (ou o antigo parâmetro do `Report`) por `$this->report`.
        *   `$rowData` (se for um parâmetro direto) por `$this->report->rowData`.
    *   Exemplo:
        ```php
        class SomeElement extends Element
        {
            // ...
            public function generate() // Sem parâmetros
            {
                $data = $this->objElement;
                $reportInstance = $this->report; // Acessa a instância do Report
                $rowData = $this->report->rowData; // Acessa os dados da linha atual

                // ... usar $reportInstance e $rowData conforme necessário ...

                parent::generate(); // Sem parâmetros
            }
        }
        ```

**Lista de Arquivos a Refatorar:**

*   `src/elements/Band.php`
*   `src/elements/BottomPen.php`
*   `src/elements/Breaker.php`
*   `src/elements/ColumnFooter.php`
*   `src/elements/ColumnHeader.php`
*   `src/elements/ComponentElement.php`
*   `src/elements/Detail.php`
*   `src/elements/Frame.php`
*   `src/elements/GroupFooter.php`
*   `src/elements/GroupHeader.php`
*   `src/elements/Image.php`
*   `src/elements/Line.php`
*   `src/elements/PageFooter.php`
*   `src/elements/PageHeader.php`
*   `src/elements/Rectangle.php`
*   `src/elements/StaticText.php`
*   `src/elements/Summary.php`
*   `src/elements/Table.php`
*   `src/elements/Title.php`

**Verificação:**

Após cada refatoração (ou em lotes, se preferir), é crucial executar os testes unitários do projeto para garantir que as mudanças não introduziram regressões. O comando para isso é:

```bash
vendor/bin/phpunit --configuration phpunit.xml.dist
```
