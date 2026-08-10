import type { NextConfig } from 'next';

const nextConfig: NextConfig = {
  reactStrictMode: true,
  // imagem enxuta no Easypanel: só o server + assets, sem node_modules inteiro
  output: 'standalone',
};

export default nextConfig;
