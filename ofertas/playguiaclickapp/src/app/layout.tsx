import type { Metadata, Viewport } from "next";
import "./globals.css";
import Script from "next/script";
import { Suspense } from "react";
import { UtmHandler } from "@/components/tracking/utm-handler";
// import { headers } from 'next/headers'; // Removido se não usado em mais nada, mas getPixels foi removido antes, então ok.

export const metadata: Metadata = {
  title: "Centro de Recarga Free Fire",
  description:
    "O site oficial para comprar diamantes no Free Fire.",
};

export const viewport: Viewport = {
  width: "device-width",
  initialScale: 1,
};

export default async function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="pt-BR">
      <head>
        <Script id="gtm-script-head" strategy="afterInteractive">
          {`
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','${process.env.NEXT_PUBLIC_GTM_ID}');
          `}
        </Script>
        {/* BlackCat Pagamentos - Script de tokenização de cartões */}
        <Script 
          src="https://assets.blackcatpagamentos.com/blackcat.js" 
          strategy="beforeInteractive"
        />
        <link rel="icon" href="/images/favicon.ico" sizes="any" />
      </head>
      <body className="max-w-md mx-auto min-h-screen">
        <noscript>
          <iframe
            src={`https://www.googletagmanager.com/ns.html?id=${process.env.NEXT_PUBLIC_GTM_ID}`}
            height="0"
            width="0"
            style={{ display: "none", visibility: "hidden" }}
          ></iframe>
        </noscript>
        
        <Suspense fallback={null}>
          <UtmHandler />
        </Suspense>
        
        {children}
      </body>
    </html>
  );
}
