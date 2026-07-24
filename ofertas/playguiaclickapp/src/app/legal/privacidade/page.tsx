import { Footer } from "@/components/ui/footer";
import { Header } from "@/components/ui/header";

export default function PrivacidadePage() {
  return (
    <div className="bg-white min-h-screen">
      <Header showCloseButton={true} />
      <main className="p-6 md:p-10 max-w-4xl mx-auto text-gray-800">
        <h1 className="text-3xl font-bold mb-6">Política de Privacidade</h1>
        
        <p className="mb-4">
          A sua privacidade é importante para nós. É política do nosso site respeitar a sua privacidade em relação a qualquer informação sua que possamos coletar.
        </p>

        <h2 className="text-2xl font-bold mt-6 mb-4">1. Informações que Coletamos</h2>
        <p className="mb-4">
          Coletamos informações pessoais que você nos fornece diretamente, como nome, e-mail e informações de pagamento quando você realiza uma compra. Também coletamos dados sobre como você usa nosso site, como páginas visitadas e interações.
        </p>

        <h2 className="text-2xl font-bold mt-6 mb-4">2. Uso de Suas Informações</h2>
        <p className="mb-4">
          Usamos as informações que coletamos para operar e manter nosso site, processar suas transações, nos comunicar com você e personalizar sua experiência.
        </p>

        <h2 className="text-2xl font-bold mt-6 mb-4">3. Compartilhamento de Informações</h2>
        <p className="mb-4">
          Não compartilhamos suas informações pessoais com terceiros, exceto para cumprir a lei ou proteger nossos direitos. Podemos compartilhar dados com processadores de pagamento para concluir transações.
        </p>

        <h2 className="text-2xl font-bold mt-6 mb-4">4. Cookies</h2>
        <p className="mb-4">
          Utilizamos cookies para melhorar a sua experiência no nosso site. Cookies são pequenos arquivos de dados armazenados no seu dispositivo. Você pode configurar seu navegador para recusar cookies, mas algumas partes do site podem não funcionar corretamente.
        </p>

        <p className="mt-8">
          Se você tiver alguma dúvida sobre como lidamos com os dados do usuário e informações pessoais, entre em contato conosco.
        </p>

        <p className="mt-4">
          Última atualização: 25 de Maio de 2024
        </p>
      </main>
      <Footer />
    </div>
  );
} 