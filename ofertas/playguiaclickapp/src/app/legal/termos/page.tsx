import { Footer } from "@/components/ui/footer";
import { Header } from "@/components/ui/header";

export default function TermosPage() {
  return (
    <div className="bg-white min-h-screen">
      <Header showCloseButton={true} />
      <main className="p-6 md:p-10 max-w-4xl mx-auto text-gray-800">
        <h1 className="text-3xl font-bold mb-6">Termos e Condições</h1>
        
        <p className="mb-4">
          Bem-vindo aos nossos Termos e Condições. Este documento rege o seu uso do nosso site e serviços. Ao acessar ou usar nosso site, você concorda em cumprir estes termos.
        </p>

        <h2 className="text-2xl font-bold mt-6 mb-4">1. Uso do Site</h2>
        <p className="mb-4">
          Você concorda em usar este site apenas para fins legais e de maneira que não infrinja os direitos de, restrinja ou iniba o uso e gozo deste site por qualquer terceiro.
        </p>

        <h2 className="text-2xl font-bold mt-6 mb-4">2. Compras e Pagamentos</h2>
        <p className="mb-4">
          Todas as compras feitas através do nosso site estão sujeitas à nossa Política de Envio e Devolução. Os preços dos produtos estão sujeitos a alterações sem aviso prévio.
        </p>

        <h2 className="text-2xl font-bold mt-6 mb-4">3. Propriedade Intelectual</h2>
        <p className="mb-4">
          Todo o conteúdo incluído neste site, como texto, gráficos, logotipos e imagens, é de nossa propriedade ou de nossos fornecedores de conteúdo e protegido por leis de direitos autorais.
        </p>

        <h2 className="text-2xl font-bold mt-6 mb-4">4. Limitação de Responsabilidade</h2>
        <p className="mb-4">
          Não seremos responsáveis por quaisquer danos diretos, indiretos, incidentais, especiais ou consequentes resultantes do uso ou da incapacidade de usar nosso site.
        </p>
        
        <p className="mt-8">
          Estes termos são regidos pelas leis do Brasil.
        </p>

        <p className="mt-4">
          Última atualização: 25 de Maio de 2024
        </p>
      </main>
      <Footer />
    </div>
  );
} 