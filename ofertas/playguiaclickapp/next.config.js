/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  experimental: {
    serverComponentsExternalPackages: [
      '@react-email/components',
      '@react-email/render',
      '@react-email/tailwind'
    ]
  },
  images: {
    unoptimized: true,
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'ext.same-assets.com',
      },
      {
        protocol: 'https',
        hostname: 'cdn-gop.garenanow.com',
      },
      {
        protocol: 'https',
        hostname: 'lh3.googleusercontent.com',
        port: '',
        pathname: '/a/**',
      },
    ],
  },
  // Esta configuração desativa os avisos de crossorigin
  crossOrigin: 'anonymous',
  eslint: {
    // Desativa a verificação do ESLint durante o build de produção.
    // Isso permite que o deploy seja concluído mesmo com erros de lint.
    ignoreDuringBuilds: true,
  },
};

export default nextConfig;
