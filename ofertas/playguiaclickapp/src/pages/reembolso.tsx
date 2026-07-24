import Head from 'next/head';

const ReembolsoPage = () => {
  return (
    <>
      <Head>
        <title>Política de Reembolso</title>
        <meta name="robots" content="noindex,nofollow" />
      </Head>
      <div className="bg-gray-50 min-h-screen">
        <div className="container mx-auto px-4 py-8 max-w-4xl">
          <h1 className="text-3xl font-bold text-center mb-6 text-gray-800">Política de Reembolso</h1>
          <div className="bg-white p-8 rounded-lg shadow-md text-base text-gray-700 leading-relaxed">
            <p className="mb-4">
              Esta política descreve como nossa equipe lida com reembolsos para infoprodutos (produtos digitais, como cursos, e-books, planilhas, etc.) adquiridos através do nosso site.
            </p>
            
            <h2 className="text-2xl font-semibold mt-8 mb-4 border-b pb-2">1. Condições para Reembolso</h2>
            <p className="mb-4">
              O cliente tem o direito de solicitar o reembolso total do valor pago por um infoproduto em até <strong>7 (sete) dias corridos</strong> após a data da compra, conforme previsto pelo Código de Defesa do Consumidor (Art. 49).
            </p>
            <p className="mb-4">
              Para ser elegível ao reembolso, o cliente deve entrar em contato através do nosso canal de suporte oficial dentro do prazo estipulado.
            </p>

            <h2 className="text-2xl font-semibold mt-8 mb-4 border-b pb-2">2. Como Solicitar o Reembolso</h2>
            <p className="mb-4">
              A solicitação de reembolso deve ser feita exclusivamente através do nosso e-mail de suporte: <strong>[Email de Suporte]</strong>.
            </p>
            <p className="mb-4">
              No e-mail, o cliente deve fornecer as seguintes informações:
            </p>
            <ul className="list-disc list-inside mb-4 pl-4 space-y-2">
              <li>Nome Completo</li>
              <li>Endereço de e-mail utilizado na compra</li>
              <li>Nome do produto adquirido</li>
              <li>Data da compra</li>
              <li>Motivo da solicitação (opcional, mas nos ajuda a melhorar)</li>
            </ul>

            <h2 className="text-2xl font-semibold mt-8 mb-4 border-b pb-2">3. Processamento do Reembolso</h2>
            <p className="mb-4">
              Após o recebimento da solicitação, nossa equipe irá analisá-la em até <strong>5 (cinco) dias úteis</strong>.
            </p>
            <p className="mb-4">
              Se a solicitação estiver de acordo com nossas políticas, o reembolso será processado. O valor será estornado através do mesmo método de pagamento utilizado na compra.
            </p>
            <p className="mb-4">
              O prazo para que o valor seja creditado na fatura do cartão de crédito ou conta bancária do cliente pode variar de acordo com a operadora do cartão e o banco.
            </p>

            <h2 className="text-2xl font-semibold mt-8 mb-4 border-b pb-2">4. Exceções</h2>
            <p className="mb-4">
              Após o período de 7 dias, não serão realizados reembolsos, exceto em casos de defeito comprovado no produto que não seja resolvido pela nossa equipe de suporte em um prazo razoável.
            </p>
            <p className="mb-4">
              Casos excepcionais podem ser analisados individualmente a nosso critério.
            </p>

            <h2 className="text-2xl font-semibold mt-8 mb-4 border-b pb-2">5. Contato</h2>
            <p className="mb-4">
              Para qualquer dúvida sobre nossa política de reembolso, entre em contato pelo e-mail: <strong>[seu-email-de-suporte@dominio.com]</strong>.
            </p>

            <div className="mt-10 pt-6 border-t border-gray-200 text-center text-sm text-gray-500">
              <p>Última atualização: 25 de Maio de 2024</p>
            </div>
          </div>
        </div>
      </div>
    </>
  );
};

export default ReembolsoPage; 