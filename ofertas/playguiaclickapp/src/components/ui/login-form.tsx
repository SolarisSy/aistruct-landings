import { Info } from "lucide-react";
import { useState, useEffect } from 'react';

interface LoginFormProps {
  onLogin: () => void;
  triggerHighlight?: boolean;
}

export function LoginForm({ onLogin, triggerHighlight }: LoginFormProps) {
  const [playerId, setPlayerId] = useState('');
  const [isPlayerIdLoginDisabled, setIsPlayerIdLoginDisabled] = useState(true);
  const [isHighlighted, setIsHighlighted] = useState(false);

  useEffect(() => {
    setIsPlayerIdLoginDisabled(playerId.trim() === '');
  }, [playerId]);

  useEffect(() => {
    if (triggerHighlight) {
      setIsHighlighted(true);
      const timer = setTimeout(() => {
        setIsHighlighted(false);
      }, 700);
      return () => clearTimeout(timer);
    }
  }, [triggerHighlight]);

  const handlePlayerIdChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setPlayerId(event.target.value);
  };

  return (
    <div className={`mx-3 mt-3 bg-white rounded-lg p-3 shadow-sm transition-all duration-300 ${isHighlighted ? 'ring-2 ring-red-500 ring-offset-2' : ''}`}>
      <div className="flex items-center mb-3">
        <div className="w-6 h-6 rounded-full bg-[#c23743] flex items-center justify-center text-white text-xs font-bold">
          1
        </div>
        <h2 className="ml-2 text-sm font-medium text-[#404756]">Login</h2>
      </div>
      <div className="relative">
        <input
          type="text"
          placeholder="Insira o ID do jogador aqui"
          className="w-full p-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-[#54b6d0] mb-3"
          value={playerId}
          onChange={handlePlayerIdChange}
        />
        <div className="absolute inset-y-0 right-2 flex items-center pointer-events-none mb-3">
          <Info className="w-4 h-4 text-gray-400" />
        </div>
      </div>
      <button
        onClick={onLogin}
        disabled={isPlayerIdLoginDisabled}
        className={`text-white py-2 px-4 rounded-md text-sm font-medium w-full ${
          isPlayerIdLoginDisabled
            ? 'bg-gray-400 cursor-not-allowed'
            : 'bg-[#c23743] hover:bg-[#a72f3a]'
        }`}
      >
        Login
      </button>
      
      <p className="mt-3 text-center">
        <span className="text-sm font-bold text-[#c23743] px-2 py-1 bg-[#fff9f9] rounded border border-[#c23743] inline-block">
          Faça login inserindo o seu ID para resgatar seu desconto!
        </span>
      </p>
    </div>
  );
}
