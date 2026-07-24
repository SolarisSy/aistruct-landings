"use client";

import { useState } from "react";

// Declaração global do BlackCat
declare global {
  interface Window {
    BlackCat: {
      tokenizeCard: (cardData: {
        number: string;
        holderName: string;
        expirationMonth: string;
        expirationYear: string;
        cvv: string;
      }) => Promise<{ token: string }>;
    };
  }
}

interface CardPaymentFormProps {
  amount: number;
  onSuccess?: (transactionId: string) => void;
  onError?: (error: string) => void;
}

export function CardPaymentForm({ amount, onSuccess, onError }: CardPaymentFormProps) {
  const [loading, setLoading] = useState(false);
  const [cardNumber, setCardNumber] = useState("");
  const [holderName, setHolderName] = useState("");
  const [expirationMonth, setExpirationMonth] = useState("");
  const [expirationYear, setExpirationYear] = useState("");
  const [cvv, setCvv] = useState("");
  const [installments, setInstallments] = useState(1);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      // 1. Tokenizar o cartão no front-end usando BlackCat.js
      if (!window.BlackCat) {
        throw new Error("BlackCat.js não foi carregado. Verifique se o script está incluído no layout.");
      }

      const tokenResponse = await window.BlackCat.tokenizeCard({
        number: cardNumber.replace(/\s/g, ""),
        holderName: holderName,
        expirationMonth: expirationMonth,
        expirationYear: expirationYear,
        cvv: cvv,
      });

      console.log("Token do cartão obtido:", tokenResponse.token);

      // 2. Enviar o token para o backend
      const response = await fetch("/api/criar-cartao", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          amount: Math.round(amount * 100), // Converter para centavos
          cardToken: tokenResponse.token,
          installments: installments,
          items: [
            {
              title: "Diamantes Free Fire",
              quantity: 1,
              unitPrice: Math.round(amount * 100),
              tangible: false,
            },
          ],
          customer: {
            name: holderName,
            email: "cliente@example.com", // Obter do formulário
            document: {
              type: "cpf",
              number: "00000000000", // Obter do formulário
            },
            phone: "11999999999", // Obter do formulário
          },
          metadata: {
            gameName: "FF",
          },
        }),
      });

      const data = await response.json();

      if (response.ok) {
        console.log("Pagamento processado:", data);
        if (onSuccess) {
          onSuccess(data.transactionId);
        }
      } else {
        throw new Error(data.error || "Erro ao processar pagamento");
      }
    } catch (error) {
      console.error("Erro ao processar cartão:", error);
      const errorMessage = error instanceof Error ? error.message : "Erro desconhecido";
      if (onError) {
        onError(errorMessage);
      }
    } finally {
      setLoading(false);
    }
  };

  const formatCardNumber = (value: string) => {
    const v = value.replace(/\s+/g, "").replace(/[^0-9]/gi, "");
    const matches = v.match(/\d{4,16}/g);
    const match = (matches && matches[0]) || "";
    const parts = [];

    for (let i = 0, len = match.length; i < len; i += 4) {
      parts.push(match.substring(i, i + 4));
    }

    if (parts.length) {
      return parts.join(" ");
    } else {
      return value;
    }
  };

  return (
    <div className="mx-3 mt-3 bg-white rounded-lg p-4 shadow-sm">
      <h2 className="text-lg font-bold mb-4">Pagamento com Cartão</h2>
      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label htmlFor="cardNumber" className="block text-sm font-medium text-gray-700 mb-1">
            Número do Cartão
          </label>
          <input
            type="text"
            id="cardNumber"
            value={cardNumber}
            onChange={(e) => setCardNumber(formatCardNumber(e.target.value))}
            placeholder="0000 0000 0000 0000"
            maxLength={19}
            required
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#c23743]"
          />
        </div>

        <div>
          <label htmlFor="holderName" className="block text-sm font-medium text-gray-700 mb-1">
            Nome no Cartão
          </label>
          <input
            type="text"
            id="holderName"
            value={holderName}
            onChange={(e) => setHolderName(e.target.value.toUpperCase())}
            placeholder="NOME COMPLETO"
            required
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#c23743]"
          />
        </div>

        <div className="grid grid-cols-3 gap-4">
          <div>
            <label htmlFor="expirationMonth" className="block text-sm font-medium text-gray-700 mb-1">
              Mês
            </label>
            <input
              type="text"
              id="expirationMonth"
              value={expirationMonth}
              onChange={(e) => setExpirationMonth(e.target.value.replace(/\D/g, ""))}
              placeholder="MM"
              maxLength={2}
              required
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#c23743]"
            />
          </div>

          <div>
            <label htmlFor="expirationYear" className="block text-sm font-medium text-gray-700 mb-1">
              Ano
            </label>
            <input
              type="text"
              id="expirationYear"
              value={expirationYear}
              onChange={(e) => setExpirationYear(e.target.value.replace(/\D/g, ""))}
              placeholder="AAAA"
              maxLength={4}
              required
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#c23743]"
            />
          </div>

          <div>
            <label htmlFor="cvv" className="block text-sm font-medium text-gray-700 mb-1">
              CVV
            </label>
            <input
              type="text"
              id="cvv"
              value={cvv}
              onChange={(e) => setCvv(e.target.value.replace(/\D/g, ""))}
              placeholder="123"
              maxLength={4}
              required
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#c23743]"
            />
          </div>
        </div>

        <div>
          <label htmlFor="installments" className="block text-sm font-medium text-gray-700 mb-1">
            Parcelas
          </label>
          <select
            id="installments"
            value={installments}
            onChange={(e) => setInstallments(Number(e.target.value))}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#c23743]"
          >
            <option value={1}>1x de R$ {amount.toFixed(2)}</option>
            <option value={2}>2x de R$ {(amount / 2).toFixed(2)}</option>
            <option value={3}>3x de R$ {(amount / 3).toFixed(2)}</option>
            <option value={4}>4x de R$ {(amount / 4).toFixed(2)}</option>
            <option value={5}>5x de R$ {(amount / 5).toFixed(2)}</option>
            <option value={6}>6x de R$ {(amount / 6).toFixed(2)}</option>
          </select>
        </div>

        <button
          type="submit"
          disabled={loading}
          className="w-full py-3 px-4 bg-[#c23743] text-white font-bold rounded-md hover:bg-[#a12f39] disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
        >
          {loading ? "Processando..." : `Pagar R$ ${amount.toFixed(2)}`}
        </button>
      </form>
    </div>
  );
}

