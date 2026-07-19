<style>
*,
*::before,
*::after {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #f0f0f0;
    color: #222;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.top-banner {
    background: #eef0eb;
    color: #004080;
    padding: 35px 15px;
    text-align: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
}

.top-banner h1 {
    font-size: 2.3rem;
}

.top-banner span {
    display: block;
    margin-top: 8px;
    font-size: 1rem;
}

.wrapper {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 25px 10px;
}

.box {
    background: #fff;
    padding: 35px 25px;
    max-width: 680px;
    width: 90%;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.07);
    text-align: center;
}

.box h2 {
    color: #004080;
    margin-bottom: 16px;
}

.box p {
    margin-bottom: 25px;
    line-height: 1.5;
}

.go-btn {
    background: #ffcb05;
    color: #004080;
    padding: 12px 24px;
    border: none;
    border-radius: 25px;
    font-size: 1rem;
    font-weight: bold;
    text-decoration: none;
    display: inline-block;
    cursor: pointer;
    transition: background 0.3s;
}

.go-btn:hover {
    background: #e6b800;
}

.rodape {
    background: #ffcb05;
    color: #004080;
    text-align: center;
    padding: 25px 15px;
    font-size: 0.85rem;
    margin-top: auto;
}

.rodape a {
    color: #004080;
    margin: 0 8px;
    text-decoration: none;
}

.rodape a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .top-banner h1 {
        font-size: 1.7rem;
    }

    .box {
        padding: 22px 18px;
    }
}
</style>

<div class="top-banner">
    <h1>Consulta de Encomenda</h1>
    <span>Verifique o status da sua encomenda em instantes</span>
</div>

<section class="wrapper">
    <div class="box">
        <h2>Status da Encomenda</h2>
        <p>Confira se sua Encomenda está Retida agora. Clique no botão abaixo para ser direcionado à página de
            consultas.
        </p>

        <a href="<?php echo LINK_DOMINIO . 'acompanhe' ?>" class="go-btn">Consultar Agora</a>
    </div>
</section>

<footer class="rodape">
    <p>&copy; 2026 Portal de Encomendas. Todos os direitos reservados.</p>
    <p>
        <a href="privacidade.html">Privacidade</a> |
        <a href="termos.html">Termos</a> |
        <a href="contato.html">Contato</a>
    </p>
</footer>