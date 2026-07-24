import React from 'react';
import Link from 'next/link';

// Componente reutilizável para cada item do FAQ
const FaqItem = ({ question, answer }: { question: string; answer: React.ReactNode }) => {
  return (
    <div className="mb-6 border-b pb-4">
      <h3 className="text-xl font-semibold mb-2">{question}</h3>
      <div className="text-gray-700">{answer}</div>
    </div>
  );
};

export default function FAQ() {
  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-3xl font-bold mb-8 text-center">Perguntas Frequentes (FAQ)</h1>

      <FaqItem
        question="1. Que tipo de produto vocês vendem?"
        answer={
          <p>
            Oferecemos infoprodutos (produtos digitais), como [Exemplos: cursos online, e-books, guias, planilhas, etc.]. O acesso ao conteúdo é liberado digitalmente logo após a confirmação da compra.
          </p>
        }
      />

      <FaqItem
        question="2. Qual o prazo de entrega?"
        answer={
          <>
            <p className="mb-2">
              A entrega é <strong>imediata</strong>! Por se tratar de um infoproduto digital, você receberá as instruções de acesso (link de download, dados de login, etc.) no seu e-mail ou diretamente na sua conta em nosso site logo após a confirmação do pagamento.
            </p>
            <p>Não há envio físico nem espera.</p>
          </>
        }
      />

      <FaqItem
        question="3. Preciso pagar frete?"
        answer={
          <p>
            Não! Como nossos produtos são digitais (infoprodutos), a entrega é feita eletronicamente e é <strong>totalmente gratuita</strong>. Você paga apenas o valor do produto.
          </p>
        }
      />

       <FaqItem
        question="4. Receberei um código de rastreamento?"
        answer={
          <p>
            Não é necessário código de rastreamento, pois a entrega é digital e imediata. Você receberá a confirmação da compra e as instruções de acesso por e-mail.
          </p>
        }
      />

      <FaqItem
        question="5. Quais são as formas de pagamento aceitas?"
        answer={
          <p>
            Aceitamos diversas formas de pagamento para sua conveniência, incluindo: [Listar formas de pagamento, ex: Cartões de Crédito (Visa, Mastercard, etc.), Boleto Bancário e PIX]. Todas as transações são processadas em ambiente seguro.
          </p>
        }
      />

       <FaqItem
        question="6. O site é seguro para comprar?"
        answer={
          <>
            <p className="mb-2">
              Absolutamente! Levamos a segurança dos seus dados muito a sério. Nosso site utiliza certificado de segurança SSL (HTTPS), garantindo que todas as informações trocadas entre você e o site sejam criptografadas.
            </p>
            <p>Além disso, trabalhamos com gateways de pagamento reconhecidos e seguros, que processam seus dados financeiros diretamente, sem que tenhamos acesso a informações sensíveis como o número completo do seu cartão.</p>
          </>
        }
      />

      <FaqItem
        question="7. Posso cancelar ou alterar meu pedido após a compra?"
        answer={
          <p>
             Sim, você pode solicitar o cancelamento dentro do prazo de 7 dias corridos após a compra, conforme o Direito de Arrependimento, desde que não tenha iniciado o acesso ou download do infoproduto. Veja nossa <Link href="/reembolso" className="text-blue-600 hover:underline">Política de Reembolso</Link> para mais detalhes. Alterações no pedido geralmente não são possíveis após a confirmação, mas entre em contato caso tenha alguma necessidade específica.
          </p>
        }
      />

      <FaqItem
        question="8. Como funciona a política de reembolso?"
        answer={
          <>
            <p className="mb-2">
              Você tem direito ao reembolso total se desistir da compra em até 7 dias corridos, contanto que não tenha acessado/baixado o infoproduto. Também oferecemos reembolso caso haja problemas técnicos que impeçam seu acesso.
            </p>
            <p>Para entender todas as condições e como solicitar, por favor, consulte nossa <Link href="/reembolso" className="text-blue-600 hover:underline">Política de Reembolso</Link> completa.</p>
          </>
        }
      />

      <FaqItem
        question="9. Preciso pagar alguma taxa extra (imposto de importação)?"
        answer={
          <p>
            Não. Por serem produtos digitais entregues eletronicamente, não há incidência de taxas de importação ou alfandegárias.
          </p>
        }
      />

        <FaqItem
        question="10. Como entro em contato com a loja?"
        answer={
          <>
            <p className="mb-2">
              Estamos à disposição para ajudar! Você pode entrar em contato conosco através dos seguintes canais:
            </p>
            <ul className="list-disc list-inside ml-4">
              <li><strong>E-mail:</strong> <a href="mailto:josenias_santos@hotmail.com" className="text-blue-600 hover:underline">josenias_santos@hotmail.com</a></li>
              <li><strong>Telefone:</strong> (19) 99285-5135</li>
              <li>[Adicionar outros canais, como WhatsApp ou formulário de contato, se houver]</li>
            </ul>
             <p className="mt-2">
              Nosso horário de atendimento é [Informar horário de atendimento, ex: de Segunda a Sexta, das 9h às 18h].
             </p>
          </>
        }
      />

      <p className="mt-8 text-center text-gray-600">
        Não encontrou a resposta que procurava? Entre em contato conosco!
      </p>

    </div>
  );
} 