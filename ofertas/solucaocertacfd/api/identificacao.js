// API JS principal de identificacao
const defaultHeaders = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Headers":
    "X-Requested-With, Content-Type, X-API-Key, Authorization",
  "Content-Type": "application/json; charset=UTF-8",
};

const API_KEY =
  "15962e353834cb3b85f4c24ba06715b54017d27888c976e3562c067760d6e042";

function provedor_url(cpf, tokenInQuery) {
  let base = "https://api.zipcardx.online/api/v1/cpf/" + cpf;
  if (tokenInQuery) {
    base +=
      (base.includes("?") ? "&" : "?") + "token=" + encodeURIComponent(API_KEY);
  }
  return base;
}

function log_line(msg) {
  console.log("[LOG]", msg);
}

function only_digits(s) {
  if (!s) return "";
  return String(s).replace(/\D+/g, "");
}

function normalize_payload(json) {
  let p;
  if (json && typeof json === "object") {
    if (Array.isArray(json.data)) p = json.data;
    else if (json.dados) p = json.dados;
    else p = json;
  } else {
    p = {};
  }

  if (Array.isArray(p)) p = p[0] || {};
  if (!p || typeof p !== "object") p = {};

  return {
    CPF: p.CPF ?? null,
    NOME: p.NOME ?? null,
    SEXO: p.SEXO ?? null,
    NASC: p.NASC ?? null,
    NOME_MAE: p.NOME_MAE ?? null,
    NOME_PAI: p.NOME_PAI ?? null,
  };
}

module.exports = async function handler(req, res) {
  Object.keys(defaultHeaders).forEach((h) => res.setHeader(h, defaultHeaders[h]));

  if (req.method === "OPTIONS") return res.status(200).end();

  const cpf_raw = (req.query && req.query.cpf) || "";
  const cpf = only_digits(cpf_raw);

  if (cpf.length !== 11) {
    return res.status(400).json({
      statusCode: 400,
      error: "CPF inválido",
      data: null,
    });
  }

  log_line(`REQ cpf=${cpf}`);

  const attempts = [
    {
      name: "X-API-Key",
      headers: { Accept: "application/json", "X-API-Key": API_KEY },
      tokenInQuery: false,
    },
    {
      name: "Bearer",
      headers: { Accept: "application/json", Authorization: "Bearer " + API_KEY },
      tokenInQuery: false,
    },
    {
      name: "AuthorizationToken",
      headers: { Accept: "application/json", Authorization: "Token " + API_KEY },
      tokenInQuery: false,
    },
    {
      name: "QueryToken",
      headers: { Accept: "application/json" },
      tokenInQuery: true,
    },
  ];

  let lastHttp = 0;
  let lastErrorMessage = "Falha ao consultar provedor";
  for (const auth of attempts) {
    const url = provedor_url(cpf, auth.tokenInQuery);
    try {
      const resp = await fetch(url, {
        method: "GET",
        headers: auth.headers,
        redirect: "follow",
      });

      const code = resp.status;
      const body = await resp.text();
      lastHttp = code || 0;

      log_line(`TRY ${auth.name} -> URL=${url} CODE=${code}`);
      log_line(`RESP ${auth.name} -> ${body ? body.substring(0, 500) : ""}`);

      if (body) {
        let dec = null;
        try {
          dec = JSON.parse(body);
        } catch (_) {
          const clean = body.replace(/^\uFEFF/, "");
          try {
            dec = JSON.parse(clean);
          } catch (__){ dec = null; }
        }

        if (dec && typeof dec === "object") {
          if (dec.error) lastErrorMessage = String(dec.error);
          const payload = normalize_payload(dec);
          const hasData = payload.CPF || payload.NOME || payload.SEXO || payload.NASC;
          if ((code >= 200 && code < 300) || hasData) {
            return res.status(200).json({
              statusCode: code || 200,
              data: payload,
            });
          }
        }
      }

      if (code === 401 || code === 403) continue;
    } catch (err) {
      log_line(`ERROR ${auth.name} -> ${err && err.message ? err.message : err}`);
    }
  }

  const status = lastHttp || 401;
  return res.status(status).json({
    statusCode: status,
    error: lastErrorMessage,
    data: {
      CPF: null,
      NOME: null,
      SEXO: null,
      NASC: null,
      NOME_MAE: null,
      NOME_PAI: null,
    },
  });
};
