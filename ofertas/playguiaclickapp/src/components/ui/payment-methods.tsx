import Image from "next/image";

const paymentMethods = [
  {
    id: "pix",
    icon: "https://ext.same-assets.com/1245660031/2376517584.png",
    bonus: 104,
    promo: true,
  },
  {
    id: "nupay",
    icon: "https://cdn-gop.garenanow.com/webmain/static/payment_center/br/menu/br_nupay_mb.png",
    bonus: 104,
    promo: true,
  },
  {
    id: "picpay",
    icon: "https://ext.same-assets.com/1245660031/2188580790.png",
    bonus: 104,
    promo: true,
  },
  {
    id: "paypal",
    icon: "https://ext.same-assets.com/1245660031/2362841555.png",
    bonus: 52,
    promo: true,
  },
];

export function PaymentMethods() {
  return (
    <div className="mx-3 mt-3 bg-white rounded-lg p-3 shadow-sm">
      <div className="flex items-center mb-3">
        <div className="w-6 h-6 rounded-full bg-[#c23743] flex items-center justify-center text-white text-xs font-bold">
          3
        </div>
        <h2 className="ml-2 text-sm font-medium text-[#404756]">Método de pagamento</h2>
      </div>

      <div className="text-xs text-[#6d7584] mb-3">
        <p>Utilize sua instituição financeira para realizar o pagamento.</p>
        <p>Seus créditos caem na sua conta de jogo assim que recebermos a confirmação de pagamento.</p>
        <p>[Para FF] Além dos diamantes em bônus, você ganha + 20% de bônus em items dentro do jogo.</p>
      </div>

      <div className="space-y-2">
        {paymentMethods.map((method) => (
          <div key={method.id} className="border border-gray-300 rounded-md p-2 flex items-center justify-between">
            <div className="flex items-center">
              <div className="w-6 h-6 mr-2 relative flex-shrink-0">
                <Image
                  src={method.icon}
                  alt={method.id}
                  width={24}
                  height={24}
                  className="object-contain w-full h-full"
                  unoptimized
                />
              </div>
              <div>
                <div className="text-sm text-[#404756] font-medium">R$ 20,99</div>
                <div className="text-xs text-[#6d7584] flex items-center">
                  + Bônus {method.bonus}
                  <Image
                    src="/images/diamante.png"
                    alt="Diamante"
                    width={12}
                    height={12}
                    className="ml-1"
                    unoptimized
                  />
                </div>
              </div>
            </div>

            {method.promo && (
              <span className="promo-badge">Promo</span>
            )}
          </div>
        ))}
      </div>

      <div className="mt-3 p-2 border border-gray-300 rounded-md flex items-center justify-between bg-[#f9f9f9]">
        <div className="flex items-center">
          <Image
            src="/images/diamante.png"
            alt="Diamante"
            width={20}
            height={20}
            className="mr-2"
            unoptimized
          />
          <div>
            <div className="text-sm text-[#404756] font-medium">520 + 104</div>
          </div>
        </div>
        <div>
          <div className="text-xs text-[#6d7584]">Total:</div>
          <div className="text-sm text-[#404756] font-medium">R$ 20,99</div>
        </div>
      </div>
    </div>
  );
}
