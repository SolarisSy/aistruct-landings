<?php

class Icons
{

    public static function usuario_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM7.07 18.28c.43-.9 3.05-1.78 4.93-1.78s4.51.88 4.93 1.78C15.57 19.36 13.86 20 12 20s-3.57-.64-4.93-1.72zm11.29-1.45c-1.43-1.74-4.9-2.33-6.36-2.33s-4.93.59-6.36 2.33C4.62 15.49 4 13.82 4 12c0-4.41 3.59-8 8-8s8 3.59 8 8c0 1.82-.62 3.49-1.64 4.83zM12 6c-1.94 0-3.5 1.56-3.5 3.5S10.06 13 12 13s3.5-1.56 3.5-3.5S13.94 6 12 6zm0 5c-.83 0-1.5-.67-1.5-1.5S11.17 8 12 8s1.5.67 1.5 1.5S12.83 11 12 11z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function mais($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function mais_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M13 7h-2v4H7v2h4v4h2v-4h4v-2h-4V7zm-1-5C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function addImagem($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M18 20H4V6h9V4H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-9h-2v9zm-7.79-3.17l-1.96-2.36L5.5 18h11l-3.54-4.71zM20 4V1h-2v3h-3c.01.01 0 2 0 2h3v2.99c.01.01 2 0 2 0V6h3V4h-3z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function setaBaixo_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
        <path
            d="M12,4c4.41,0,8,3.59,8,8s-3.59,8-8,8s-8-3.59-8-8S7.59,4,12,4 M12,2C6.48,2,2,6.48,2,12c0,5.52,4.48,10,10,10 c5.52,0,10-4.48,10-10C22,6.48,17.52,2,12,2L12,2z M13,12l0-4h-2l0,4H8l4,4l4-4H13z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function setaCima_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
        <path
            d="M12,20c-4.41,0-8-3.59-8-8s3.59-8,8-8s8,3.59,8,8S16.41,20,12,20 M12,22c5.52,0,10-4.48,10-10c0-5.52-4.48-10-10-10 C6.48,2,2,6.48,2,12C2,17.52,6.48,22,12,22L12,22z M11,12l0,4h2l0-4h3l-4-4l-4,4H11z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function setaEsquerda_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <path
            d="M2,12c0,5.52,4.48,10,10,10c5.52,0,10-4.48,10-10S17.52,2,12,2C6.48,2,2,6.48,2,12z M20,12c0,4.42-3.58,8-8,8 c-4.42,0-8-3.58-8-8s3.58-8,8-8C16.42,4,20,7.58,20,12z M8,12l4-4l1.41,1.41L11.83,11H16v2h-4.17l1.59,1.59L12,16L8,12z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function setaDireita_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <path
            d="M22,12c0-5.52-4.48-10-10-10C6.48,2,2,6.48,2,12s4.48,10,10,10C17.52,22,22,17.52,22,12z M4,12c0-4.42,3.58-8,8-8 c4.42,0,8,3.58,8,8s-3.58,8-8,8C7.58,20,4,16.42,4,12z M16,12l-4,4l-1.41-1.41L12.17,13H8v-2h4.17l-1.59-1.59L12,8L16,12z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function setaDireita($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path d="M16.01 11H4v2h12.01v3L20 12l-3.99-4v3z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function clipe($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5c0-1.38 1.12-2.5 2.5-2.5s2.5 1.12 2.5 2.5v10.5c0 .55-.45 1-1 1s-1-.45-1-1V6H10v9.5c0 1.38 1.12 2.5 2.5 2.5s2.5-1.12 2.5-2.5V5c0-2.21-1.79-4-4-4S7 2.79 7 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-1.5z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function block($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM4 12c0-4.42 3.58-8 8-8 1.85 0 3.55.63 4.9 1.69L5.69 16.9C4.63 15.55 4 13.85 4 12zm8 8c-1.85 0-3.55-.63-4.9-1.69L18.31 7.1C19.37 8.45 20 10.15 20 12c0 4.42-3.58 8-8 8z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function raio($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <path
            d="M11,21h-1l1-7H7.5c-0.88,0-0.33-0.75-0.31-0.78C8.48,10.94,10.42,7.54,13.01,3h1l-1,7h3.51c0.4,0,0.62,0.19,0.4,0.66 C12.97,17.55,11,21,11,21z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function camera($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M20 4h-3.17L15 2H9L7.17 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V6h4.05l1.83-2h4.24l1.83 2H20v12zM12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zm0 8c-1.65 0-3-1.35-3-3s1.35-3 3-3 3 1.35 3 3-1.35 3-3 3z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function checkBox($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM17.99 9l-1.41-1.42-6.59 6.59-2.58-2.57-1.42 1.41 4 3.99z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function check_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm4.59-12.42L10 14.17l-2.59-2.58L6 13l4 4 8-8z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function listaCheck($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <rect fill="none" height="24" width="24" />
    <path
        d="M22,7h-9v2h9V7z M22,15h-9v2h9V15z M5.54,11L2,7.46l1.41-1.41l2.12,2.12l4.24-4.24l1.41,1.41L5.54,11z M5.54,19L2,15.46 l1.41-1.41l2.12,2.12l4.24-4.24l1.41,1.41L5.54,19z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function x($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function nuvem($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M12 6c2.62 0 4.88 1.86 5.39 4.43l.3 1.5 1.53.11c1.56.1 2.78 1.41 2.78 2.96 0 1.65-1.35 3-3 3H6c-2.21 0-4-1.79-4-4 0-2.05 1.53-3.76 3.56-3.97l1.07-.11.5-.95C8.08 7.14 9.94 6 12 6m0-2C9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96C18.67 6.59 15.64 4 12 4z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function nuvemCheck($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM19 18H6c-2.21 0-4-1.79-4-4 0-2.05 1.53-3.76 3.56-3.97l1.07-.11.5-.95C8.08 7.14 9.94 6 12 6c2.62 0 4.88 1.86 5.39 4.43l.3 1.5 1.53.11c1.56.1 2.78 1.41 2.78 2.96 0 1.65-1.35 3-3 3zm-9-3.82l-2.09-2.09L6.5 13.5 10 17l6.01-6.01-1.41-1.41z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function nuvemDownload($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM19 18H6c-2.21 0-4-1.79-4-4 0-2.05 1.53-3.76 3.56-3.97l1.07-.11.5-.95C8.08 7.14 9.94 6 12 6c2.62 0 4.88 1.86 5.39 4.43l.3 1.5 1.53.11c1.56.1 2.78 1.41 2.78 2.96 0 1.65-1.35 3-3 3zm-5.55-8h-2.9v3H8l4 4 4-4h-2.55z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function nuvemUpload($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM19 18H6c-2.21 0-4-1.79-4-4 0-2.05 1.53-3.76 3.56-3.97l1.07-.11.5-.95C8.08 7.14 9.94 6 12 6c2.62 0 4.88 1.86 5.39 4.43l.3 1.5 1.53.11c1.56.1 2.78 1.41 2.78 2.96 0 1.65-1.35 3-3 3zM8 13h2.55v3h2.9v-3H16l-4-4z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function copiar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function lapis($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM5.92 19H5v-.92l9.06-9.06.92.92L5.92 19zM20.71 5.63l-2.34-2.34c-.2-.2-.45-.29-.71-.29s-.51.1-.7.29l-1.83 1.83 3.75 3.75 1.83-1.83c.39-.39.39-1.02 0-1.41z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function dashboard($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M19 5v2h-4V5h4M9 5v6H5V5h4m10 8v6h-4v-6h4M9 17v2H5v-2h4M21 3h-8v6h8V3zM11 3H3v10h8V3zm10 8h-8v10h8V11zm-10 4H3v6h8v-6z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function lixeira($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path d="M16 9v10H8V9h8m-1.5-6h-5l-1 1H5v2h14V4h-3.5l-1-1zM18 7H6v12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function lixeira2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M15 16h4v2h-4zm0-8h7v2h-7zm0 4h6v2h-6zM3 18c0 1.1.9 2 2 2h6c1.1 0 2-.9 2-2V8H3v10zm2-8h6v8H5v-8zm5-6H6L5 5H2v2h12V5h-3z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function papel($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M8 16h8v2H8zm0-4h8v2H8zm6-10H6c-1.1 0-2 .9-2 2v16c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function diamante($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <path
            d="M19,3H5L2,9l10,12L22,9L19,3z M9.62,8l1.5-3h1.76l1.5,3H9.62z M11,10v6.68L5.44,10H11z M13,10h5.56L13,16.68V10z M19.26,8 h-2.65l-1.5-3h2.65L19.26,8z M6.24,5h2.65l-1.5,3H4.74L6.24,5z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function check($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function download($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path d="M19 9h-4V3H9v6H5l7 7 7-7zm-8 2V5h2v6h1.17L12 13.17 9.83 11H11zm-6 7h14v2H5z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function download2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <g>
            <path
                d="M18.32,4.26C16.84,3.05,15.01,2.25,13,2.05v2.02c1.46,0.18,2.79,0.76,3.9,1.62L18.32,4.26z M19.93,11h2.02 c-0.2-2.01-1-3.84-2.21-5.32L18.31,7.1C19.17,8.21,19.75,9.54,19.93,11z M18.31,16.9l1.43,1.43c1.21-1.48,2.01-3.32,2.21-5.32 h-2.02C19.75,14.46,19.17,15.79,18.31,16.9z M13,19.93v2.02c2.01-0.2,3.84-1,5.32-2.21l-1.43-1.43 C15.79,19.17,14.46,19.75,13,19.93z M15.59,10.59L13,13.17V7h-2v6.17l-2.59-2.59L7,12l5,5l5-5L15.59,10.59z M11,19.93v2.02 c-5.05-0.5-9-4.76-9-9.95s3.95-9.45,9-9.95v2.02C7.05,4.56,4,7.92,4,12S7.05,19.44,11,19.93z" />
        </g>
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function exclamacao_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path
        d="M11 15h2v2h-2v-2zm0-8h2v6h-2V7zm.99-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function coracao($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M16.5 3c-1.74 0-3.41.81-4.5 2.09C10.91 3.81 9.24 3 7.5 3 4.42 3 2 5.42 2 8.5c0 3.78 3.4 6.86 8.55 11.54L12 21.35l1.45-1.32C18.6 15.36 22 12.28 22 8.5 22 5.42 19.58 3 16.5 3zm-4.4 15.55l-.1.1-.1-.1C7.14 14.24 4 11.39 4 8.5 4 6.5 5.5 5 7.5 5c1.54 0 3.04.99 3.57 2.36h1.87C13.46 5.99 14.96 5 16.5 5c2 0 3.5 1.5 3.5 3.5 0 2.89-3.14 5.74-7.9 10.05z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function coracao2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function coracaoPulso($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M300-840q52 0 99 22t81 62q34-40 81-62t99-22q94 0 157 63t63 157q0 5-.5 10t-.5 10h-80q1-5 1-10v-10q0-60-40-100t-100-40q-47 0-87 26.5T518-666h-76q-15-41-55-67.5T300-760q-60 0-100 40t-40 100v10q0 5 1 10H81q0-5-.5-10t-.5-10q0-94 63-157t157-63Zm-88 480h112q32 31 70 67t86 79q48-43 86-79t70-67h113q-38 42-90 91T538-158l-58 52-58-52q-69-62-120.5-111T212-360Zm230 40q13 0 22.5-7.5T478-347l54-163 35 52q5 8 14 13t19 5h320v-80H623l-69-102q-6-9-15.5-13.5T518-640q-13 0-22.5 7.5T482-613l-54 162-34-51q-5-8-14-13t-19-5H40v80h297l69 102q6 9 15.5 13.5T442-320Zm38-167Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function upload($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <path
            d="M18,15v3H6v-3H4v3c0,1.1,0.9,2,2,2h12c1.1,0,2-0.9,2-2v-3H18z M7,9l1.41,1.41L11,7.83V16h2V7.83l2.59,2.58L17,9l-5-5L7,9z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function arquivo($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M9.17 6l2 2H20v10H4V6h5.17M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function lista($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M4 10.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0-6c-.83 0-1.5.67-1.5 1.5S3.17 7.5 4 7.5 5.5 6.83 5.5 6 4.83 4.5 4 4.5zm0 12c-.83 0-1.5.68-1.5 1.5s.68 1.5 1.5 1.5 1.5-.68 1.5-1.5-.67-1.5-1.5-1.5zM7 19h14v-2H7v2zm0-6h14v-2H7v2zm0-8v2h14V5H7z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function fone($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M19 14v3c0 .55-.45 1-1 1h-1v-4h2M7 14v4H6c-.55 0-1-.45-1-1v-3h2m5-13c-4.97 0-9 4.03-9 9v7c0 1.66 1.34 3 3 3h3v-8H5v-2c0-3.87 3.13-7 7-7s7 3.13 7 7v2h-4v8h3c1.66 0 3-1.34 3-3v-7c0-4.97-4.03-9-9-9z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function x_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M14.59 8L12 10.59 9.41 8 8 9.41 10.59 12 8 14.59 9.41 16 12 13.41 14.59 16 16 14.59 13.41 12 16 9.41 14.59 8zM12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function home($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path d="M12 5.69l5 4.5V18h-2v-6H9v6H7v-7.81l5-4.5M12 3L2 12h3v8h6v-6h2v6h6v-8h3L12 3z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function imagem($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M19 5v14H5V5h14m0-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-4.86 8.86l-3 3.87L9 13.14 6 17h12l-3.86-5.14z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function info_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function emoji($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function setaBaixo2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function setaCima2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6 1.41 1.41z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function setaEsquerda2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function setaDireita2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function setaEsquerda($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path d="M21 11H6.83l3.58-3.59L9 6l-6 6 6 6 1.41-1.41L6.83 13H21v-2z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function setaBaixo3($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <g>
            <polygon points="18,6.41 16.59,5 12,9.58 7.41,5 6,6.41 12,12.41" />
            <polygon points="18,13 16.59,11.59 12,16.17 7.41,11.59 6,13 12,19" />
        </g>
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function setaCima3($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <g>
            <polygon points="6,17.59 7.41,19 12,14.42 16.59,19 18,17.59 12,11.59" />
            <polygon points="6,11 7.41,12.41 12,7.83 16.59,12.41 18,11 12,5" />
        </g>
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function setaEsquerda3($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <g>
            <polygon points="17.59,18 19,16.59 14.42,12 19,7.41 17.59,6 11.59,12" />
            <polygon points="11,18 12.41,16.59 7.83,12 12.41,7.41 11,6 5,12" />
        </g>
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function setaDireita3($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <g>
            <polygon points="6.41,6 5,7.41 9.58,12 5,16.59 6.41,18 12.41,12" />
            <polygon points="13,6 11.59,7.41 16.17,12 11.59,16.59 13,18 19,12" />
        </g>
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function launch($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function lista2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <g fill="none">
        <path d="M0 0h24v24H0V0z" />
        <path d="M0 0h24v24H0V0z" opacity=".87" />
    </g>
    <path
        d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7zm-4 6h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function entrar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <path
            d="M11,7L9.6,8.4l2.6,2.6H2v2h10.2l-2.6,2.6L11,17l5-5L11,7z M20,19h-8v2h8c1.1,0,2-0.9,2-2V5c0-1.1-0.9-2-2-2h-8v2h8V19z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function sair($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <path d="M0,0h24v24H0V0z" fill="none" />
    </g>
    <g>
        <path
            d="M17,8l-1.41,1.41L17.17,11H9v2h8.17l-1.58,1.58L17,16l4-4L17,8z M5,5h7V3H5C3.9,3,3,3.9,3,5v14c0,1.1,0.9,2,2,2h7v-2H5V5z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function menu($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function emojiMal($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 2.5c-2.33 0-4.31 1.46-5.11 3.5h10.22c-.8-2.04-2.78-3.5-5.11-3.5z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function pontos3Horizontal($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M6 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm12 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-6 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function pontos3Vertical($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function semUsuario_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <g>
            <path
                d="M15.18,10.94c0.2-0.44,0.32-0.92,0.32-1.44C15.5,7.57,13.93,6,12,6c-0.52,0-1,0.12-1.44,0.32L15.18,10.94z" />
            <path
                d="M12,2C6.48,2,2,6.48,2,12s4.48,10,10,10s10-4.48,10-10S17.52,2,12,2z M4,12c0-1.85,0.63-3.55,1.69-4.9l2.86,2.86 c0.21,1.56,1.43,2.79,2.99,2.99l2.2,2.2C13.17,15.05,12.59,15,12,15c-2.32,0-4.45,0.8-6.14,2.12C4.7,15.73,4,13.95,4,12z M12,20 c-1.74,0-3.34-0.56-4.65-1.5C8.66,17.56,10.26,17,12,17s3.34,0.56,4.65,1.5C15.34,19.44,13.74,20,12,20z M18.31,16.9L7.1,5.69 C8.45,4.63,10.15,4,12,4c4.42,0,8,3.58,8,8C20,13.85,19.37,15.54,18.31,16.9z" />
        </g>
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function usuario($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M12 6c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2m0 9c2.7 0 5.8 1.29 6 2v1H6v-.99c.2-.72 3.3-2.01 6-2.01m0-11C9.79 4 8 5.79 8 8s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 9c-2.67 0-8 1.34-8 4v3h16v-3c0-2.66-5.33-4-8-4z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function celular($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M16 1H8C6.34 1 5 2.34 5 4v16c0 1.66 1.34 3 3 3h8c1.66 0 3-1.34 3-3V4c0-1.66-1.34-3-3-3zm1 17H7V4h10v14zm-3 3h-4v-1h4v1z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function pix($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" y="0" />
    </g>
    <g>
        <g>
            <path
                d="M15.45,16.52l-3.01-3.01c-0.11-0.11-0.24-0.13-0.31-0.13s-0.2,0.02-0.31,0.13L8.8,16.53c-0.34,0.34-0.87,0.89-2.64,0.89 l3.71,3.7c1.17,1.17,3.07,1.17,4.24,0l3.72-3.71C16.92,17.41,16.16,17.23,15.45,16.52z" />
            <path
                d="M8.8,7.47l3.02,3.02c0.08,0.08,0.2,0.13,0.31,0.13s0.23-0.05,0.31-0.13l2.99-2.99c0.71-0.74,1.52-0.91,2.43-0.91 l-3.72-3.71c-1.17-1.17-3.07-1.17-4.24,0l-3.71,3.7C7.95,6.58,8.49,7.16,8.8,7.47z" />
            <path
                d="M21.11,9.85l-2.25-2.26H17.6c-0.54,0-1.08,0.22-1.45,0.61l-3,3c-0.28,0.28-0.65,0.42-1.02,0.42 c-0.36,0-0.74-0.15-1.02-0.42L8.09,8.17c-0.38-0.38-0.9-0.6-1.45-0.6H5.17l-2.29,2.3c-1.17,1.17-1.17,3.07,0,4.24l2.29,2.3h1.48 c0.54,0,1.06-0.22,1.45-0.6l3.02-3.02c0.28-0.28,0.65-0.42,1.02-0.42c0.37,0,0.74,0.14,1.02,0.42l3.01,3.01 c0.38,0.38,0.9,0.6,1.45,0.6h1.26l2.25-2.26C22.3,12.96,22.3,11.04,21.11,9.85z" />
        </g>
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function localizacao($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0z" fill="none" />
    <path
        d="M12 12c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm6-1.8C18 6.57 15.35 4 12 4s-6 2.57-6 6.2c0 2.34 1.95 5.44 6 9.14 4.05-3.7 6-6.8 6-9.14zM12 2c4.2 0 8 3.22 8 8.2 0 3.32-2.67 7.25-8 11.8-5.33-4.55-8-8.48-8-11.8C4 5.22 7.8 2 12 2z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function globo($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM4 12c0-.61.08-1.21.21-1.78L8.99 15v1c0 1.1.9 2 2 2v1.93C7.06 19.43 4 16.07 4 12zm13.89 5.4c-.26-.81-1-1.4-1.9-1.4h-1v-3c0-.55-.45-1-1-1h-6v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41C17.92 5.77 20 8.65 20 12c0 2.08-.81 3.98-2.11 5.4z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function globo_off($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
        <path
            d="M11,8.17L6.49,3.66C8.07,2.61,9.96,2,12,2c5.52,0,10,4.48,10,10c0,2.04-0.61,3.93-1.66,5.51l-1.46-1.46 C19.59,14.87,20,13.48,20,12c0-3.35-2.07-6.22-5-7.41V5c0,1.1-0.9,2-2,2h-2V8.17z M21.19,21.19l-1.41,1.41l-2.27-2.27 C15.93,21.39,14.04,22,12,22C6.48,22,2,17.52,2,12c0-2.04,0.61-3.93,1.66-5.51L1.39,4.22l1.41-1.41L21.19,21.19z M11,18 c-1.1,0-2-0.9-2-2v-1l-4.79-4.79C4.08,10.79,4,11.38,4,12c0,4.08,3.05,7.44,7,7.93V18z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function recarregar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function menos_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M7 11v2h10v-2H7zm5-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function problema($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path d="M12 5.99L19.53 19H4.47L12 5.99M12 2L1 21h22L12 2zm1 14h-2v2h2v-2zm0-6h-2v4h2v-4z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function foguete($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <g>
            <g>
                <path
                    d="M14,11c0-1.1-0.9-2-2-2s-2,0.9-2,2s0.9,2,2,2S14,12.1,14,11z M7.98,18.25c-0.29-0.9-0.57-1.94-0.76-3L6,16.07v2.98 L7.98,18.25z M12,2c0,0,5,2,5,11l2.11,1.41c0.56,0.37,0.89,1,0.89,1.66V22l-5-2H9l-5,2v-5.93c0-0.67,0.33-1.29,0.89-1.66L7,13 C7,4,12,2,12,2z M12,4.36c0,0-3,2.02-3,8.64c0,2.25,1,5,1,5h4c0,0,1-2.75,1-5C15,6.38,12,4.36,12,4.36z M18,19.05v-2.98 l-1.22-0.81c-0.19,1.05-0.47,2.1-0.76,3L18,19.05z" />
            </g>
        </g>
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function horas($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function frasco($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <path
            d="M13,11.33L18,18H6l5-6.67V6h2 M15.96,4H8.04C7.62,4,7.39,4.48,7.65,4.81L9,6.5v4.17L3.2,18.4C2.71,19.06,3.18,20,4,20h16 c0.82,0,1.29-0.94,0.8-1.6L15,10.67V6.5l1.35-1.69C16.61,4.48,16.38,4,15.96,4L15.96,4z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function pesquisar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function pesquisar_off($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><g><rect fill="none" height="24" width="24"/></g><g><g><path d="M15.5,14h-0.79l-0.28-0.27C15.41,12.59,16,11.11,16,9.5C16,5.91,13.09,3,9.5,3C6.08,3,3.28,5.64,3.03,9h2.02 C5.3,6.75,7.18,5,9.5,5C11.99,5,14,7.01,14,9.5S11.99,14,9.5,14c-0.17,0-0.33-0.03-0.5-0.05v2.02C9.17,15.99,9.33,16,9.5,16 c1.61,0,3.09-0.59,4.23-1.57L14,14.71v0.79l5,4.99L20.49,19L15.5,14z"/><polygon points="6.47,10.82 4,13.29 1.53,10.82 0.82,11.53 3.29,14 0.82,16.47 1.53,17.18 4,14.71 6.47,17.18 7.18,16.47 4.71,14 7.18,11.53"/></g></g></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function emojiTriste($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <circle cx="15.5" cy="9.5" r="1.5" />
    <circle cx="8.5" cy="9.5" r="1.5" />
    <path
        d="M12 14c-2.33 0-4.32 1.45-5.12 3.5h1.67c.69-1.19 1.97-2 3.45-2s2.75.81 3.45 2h1.67c-.8-2.05-2.79-3.5-5.12-3.5zm-.01-12C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function emojiFeliz($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <circle cx="15.5" cy="9.5" r="1.5" />
    <circle cx="8.5" cy="9.5" r="1.5" />
    <path
        d="M12 16c-1.48 0-2.75-.81-3.45-2H6.88c.8 2.05 2.79 3.5 5.12 3.5s4.32-1.45 5.12-3.5h-1.67c-.7 1.19-1.97 2-3.45 2zm-.01-14C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function engrenajem($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M19.43 12.98c.04-.32.07-.64.07-.98 0-.34-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.09-.16-.26-.25-.44-.25-.06 0-.12.01-.17.03l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.06-.02-.12-.03-.18-.03-.17 0-.34.09-.43.25l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98 0 .33.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.09.16.26.25.44.25.06 0 .12-.01.17-.03l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.06.02.12.03.18.03.17 0 .34-.09.43-.25l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zm-1.98-1.71c.04.31.05.52.05.73 0 .21-.02.43-.05.73l-.14 1.13.89.7 1.08.84-.7 1.21-1.27-.51-1.04-.42-.9.68c-.43.32-.84.56-1.25.73l-1.06.43-.16 1.13-.2 1.35h-1.4l-.19-1.35-.16-1.13-1.06-.43c-.43-.18-.83-.41-1.23-.71l-.91-.7-1.06.43-1.27.51-.7-1.21 1.08-.84.89-.7-.14-1.13c-.03-.31-.05-.54-.05-.74s.02-.43.05-.73l.14-1.13-.89-.7-1.08-.84.7-1.21 1.27.51 1.04.42.9-.68c.43-.32.84-.56 1.25-.73l1.06-.43.16-1.13.2-1.35h1.39l.19 1.35.16 1.13 1.06.43c.43.18.83.41 1.23.71l.91.7 1.06-.43 1.27-.51.7 1.21-1.07.85-.89.7.14 1.13zM12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 6c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function carrinhoDeCompras($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M15.55 13c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.37-.66-.11-1.48-.87-1.48H5.21l-.94-2H1v2h2l3.6 7.59-1.35 2.44C4.52 15.37 5.48 17 7 17h12v-2H7l1.1-2h7.45zM6.16 6h12.15l-2.76 5H8.53L6.16 6zM7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function estrela2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <path d="M0,0h24v24H0V0z" fill="none" />
        <path d="M0,0h24v24H0V0z" fill="none" />
    </g>
    <g>
        <path d="M12,17.27L18.18,21l-1.64-7.03L22,9.24l-7.19-0.61L12,2L9.19,8.63L2,9.24l5.46,4.73L5.82,21L12,17.27z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function estrela($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M22 9.24l-7.19-.62L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.63-7.03L22 9.24zM12 15.4l-3.76 2.27 1-4.28-3.32-2.88 4.38-.38L12 6.1l1.71 4.04 4.38.38-3.32 2.88 1 4.28L12 15.4z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function legal($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0zm0 0h24v24H0V0z" fill="none" />
    <path
        d="M9 21h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 1 7.58 7.59C7.22 7.95 7 8.45 7 9v10c0 1.1.9 2 2 2zM9 9l4.34-4.34L12 10h9v2l-3 7H9V9zM1 9h4v12H1z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function lista3($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0zm0 0h24v24H0V0z" fill="none" />
    <path d="M3 9h14V7H3v2zm0 4h14v-2H3v2zm0 4h14v-2H3v2zm16 0h2v-2h-2v2zm0-10v2h2V7h-2zm0 6h2v-2h-2v2z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function interruptorDesligado($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M17 6H7c-3.31 0-6 2.69-6 6s2.69 6 6 6h10c3.31 0 6-2.69 6-6s-2.69-6-6-6zm0 10H7c-2.21 0-4-1.79-4-4s1.79-4 4-4h10c2.21 0 4 1.79 4 4s-1.79 4-4 4zM7 9c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function interruptorLigado($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0z" fill="none" />
    <path
        d="M17 6H7c-3.31 0-6 2.69-6 6s2.69 6 6 6h10c3.31 0 6-2.69 6-6s-2.69-6-6-6zm0 10H7c-2.21 0-4-1.79-4-4s1.79-4 4-4h10c2.21 0 4 1.79 4 4s-1.79 4-4 4zm0-7c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function olho($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M12 6c3.79 0 7.17 2.13 8.82 5.5C19.17 14.87 15.79 17 12 17s-7.17-2.13-8.82-5.5C4.83 8.13 8.21 6 12 6m0-2C7 4 2.73 7.11 1 11.5 2.73 15.89 7 19 12 19s9.27-3.11 11-7.5C21.27 7.11 17 4 12 4zm0 5c1.38 0 2.5 1.12 2.5 2.5S13.38 14 12 14s-2.5-1.12-2.5-2.5S10.62 9 12 9m0-2c-2.48 0-4.5 2.02-4.5 4.5S9.52 16 12 16s4.5-2.02 4.5-4.5S14.48 7 12 7z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function olho_off($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0zm0 0h24v24H0V0zm0 0h24v24H0V0zm0 0h24v24H0V0z" fill="none" />
    <path
        d="M12 6c3.79 0 7.17 2.13 8.82 5.5-.59 1.22-1.42 2.27-2.41 3.12l1.41 1.41c1.39-1.23 2.49-2.77 3.18-4.53C21.27 7.11 17 4 12 4c-1.27 0-2.49.2-3.64.57l1.65 1.65C10.66 6.09 11.32 6 12 6zm-1.07 1.14L13 9.21c.57.25 1.03.71 1.28 1.28l2.07 2.07c.08-.34.14-.7.14-1.07C16.5 9.01 14.48 7 12 7c-.37 0-.72.05-1.07.14zM2.01 3.87l2.68 2.68C3.06 7.83 1.77 9.53 1 11.5 2.73 15.89 7 19 12 19c1.52 0 2.98-.29 4.32-.82l3.42 3.42 1.41-1.41L3.42 2.45 2.01 3.87zm7.5 7.5l2.61 2.61c-.04.01-.08.02-.12.02-1.38 0-2.5-1.12-2.5-2.5 0-.05.01-.08.01-.13zm-3.4-3.4l1.75 1.75c-.23.55-.36 1.15-.36 1.78 0 2.48 2.02 4.5 4.5 4.5.63 0 1.23-.13 1.77-.36l.98.98c-.88.24-1.8.38-2.75.38-3.79 0-7.17-2.13-8.82-5.5.7-1.43 1.72-2.61 2.93-3.53z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function whatsapp($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" y="0" />
    </g>
    <g>
        <g>
            <g>
                <path
                    d="M19.05,4.91C17.18,3.03,14.69,2,12.04,2c-5.46,0-9.91,4.45-9.91,9.91c0,1.75,0.46,3.45,1.32,4.95L2.05,22l5.25-1.38 c1.45,0.79,3.08,1.21,4.74,1.21h0c0,0,0,0,0,0c5.46,0,9.91-4.45,9.91-9.91C21.95,9.27,20.92,6.78,19.05,4.91z M12.04,20.15 L12.04,20.15c-1.48,0-2.93-0.4-4.2-1.15l-0.3-0.18l-3.12,0.82l0.83-3.04l-0.2-0.31c-0.82-1.31-1.26-2.83-1.26-4.38 c0-4.54,3.7-8.24,8.24-8.24c2.2,0,4.27,0.86,5.82,2.42c1.56,1.56,2.41,3.63,2.41,5.83C20.28,16.46,16.58,20.15,12.04,20.15z M16.56,13.99c-0.25-0.12-1.47-0.72-1.69-0.81c-0.23-0.08-0.39-0.12-0.56,0.12c-0.17,0.25-0.64,0.81-0.78,0.97 c-0.14,0.17-0.29,0.19-0.54,0.06c-0.25-0.12-1.05-0.39-1.99-1.23c-0.74-0.66-1.23-1.47-1.38-1.72c-0.14-0.25-0.02-0.38,0.11-0.51 c0.11-0.11,0.25-0.29,0.37-0.43c0.12-0.14,0.17-0.25,0.25-0.41c0.08-0.17,0.04-0.31-0.02-0.43c-0.06-0.12-0.56-1.34-0.76-1.84 c-0.2-0.48-0.41-0.42-0.56-0.43C8.86,7.33,8.7,7.33,8.53,7.33c-0.17,0-0.43,0.06-0.66,0.31C7.65,7.89,7.01,8.49,7.01,9.71 c0,1.22,0.89,2.4,1.01,2.56c0.12,0.17,1.75,2.67,4.23,3.74c0.59,0.26,1.05,0.41,1.41,0.52c0.59,0.19,1.13,0.16,1.56,0.1 c0.48-0.07,1.47-0.6,1.67-1.18c0.21-0.58,0.21-1.07,0.14-1.18S16.81,14.11,16.56,13.99z" />
            </g>
        </g>
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function checkAdicionar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <rect fill="none" height="24" width="24" />
    <path
        d="M22,5.18L10.59,16.6l-4.24-4.24l1.41-1.41l2.83,2.83l10-10L22,5.18z M12,20c-4.41,0-8-3.59-8-8s3.59-8,8-8 c1.57,0,3.04,0.46,4.28,1.25l1.45-1.45C16.1,2.67,14.13,2,12,2C6.48,2,2,6.48,2,12s4.48,10,10,10c1.73,0,3.36-0.44,4.78-1.22 l-1.5-1.5C14.28,19.74,13.17,20,12,20z M19,15h-3v2h3v3h2v-3h3v-2h-3v-3h-2V15z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function livro($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM9 4h2v5l-1-.75L9 9V4zm9 16H6V4h1v9l3-2.25L13 13V4h5v16z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function livrAberto($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M260-320q47 0 91.5 10.5T440-278v-394q-41-24-87-36t-93-12q-36 0-71.5 7T120-692v396q35-12 69.5-18t70.5-6Zm260 42q44-21 88.5-31.5T700-320q36 0 70.5 6t69.5 18v-396q-33-14-68.5-21t-71.5-7q-47 0-93 12t-87 36v394Zm-40 118q-48-38-104-59t-116-21q-42 0-82.5 11T100-198q-21 11-40.5-1T40-234v-482q0-11 5.5-21T62-752q46-24 96-36t102-12q58 0 113.5 15T480-740q51-30 106.5-45T700-800q52 0 102 12t96 36q11 5 16.5 15t5.5 21v482q0 23-19.5 35t-40.5 1q-37-20-77.5-31T700-240q-60 0-116 21t-104 59ZM280-494Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function ferramenta($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M22.61 18.99l-9.08-9.08c.93-2.34.45-5.1-1.44-7C9.79.61 6.21.4 3.66 2.26L7.5 6.11 6.08 7.52 2.25 3.69C.39 6.23.6 9.82 2.9 12.11c1.86 1.86 4.57 2.35 6.89 1.48l9.11 9.11c.39.39 1.02.39 1.41 0l2.3-2.3c.4-.38.4-1.01 0-1.41zm-3 1.6l-9.46-9.46c-.61.45-1.29.72-2 .82-1.36.2-2.79-.21-3.83-1.25C3.37 9.76 2.93 8.5 3 7.26l3.09 3.09 4.24-4.24-3.09-3.09c1.24-.07 2.49.37 3.44 1.31 1.08 1.08 1.49 2.57 1.24 3.96-.12.71-.42 1.37-.88 1.96l9.45 9.45-.88.89z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function calendario($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M20 3h-1V1h-2v2H7V1H5v2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 18H4V10h16v11zm0-13H4V5h16v3z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function calendario2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M7 11h2v2H7v-2zm14-5v14c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2l.01-14c0-1.1.88-2 1.99-2h1V2h2v2h8V2h2v2h1c1.1 0 2 .9 2 2zM5 8h14V6H5v2zm14 12V10H5v10h14zm-4-7h2v-2h-2v2zm-4 0h2v-2h-2v2z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function calendarioCheck($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M438-226 296-368l58-58 84 84 168-168 58 58-226 226ZM200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm0-480h560v-80H200v80Zm0 0v-80 80Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function sino_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <path d="M0,0h24v24H0V0z" fill="none" />
    </g>
    <g>
        <path
            d="M12,18.5c0.83,0,1.5-0.67,1.5-1.5h-3C10.5,17.83,11.17,18.5,12,18.5z M12,2C6.48,2,2,6.48,2,12s4.48,10,10,10 c5.52,0,10-4.48,10-10S17.52,2,12,2z M12,20c-4.41,0-8-3.59-8-8s3.59-8,8-8c4.41,0,8,3.59,8,8S16.41,20,12,20z M16,11.39 c0-2.11-1.03-3.92-3-4.39V6.5c0-0.57-0.43-1-1-1s-1,0.43-1,1V7c-1.97,0.47-3,2.27-3,4.39V14H7v2h10v-2h-1V11.39z M14,14h-4v-3 c0-1.1,0.9-2,2-2s2,0.9,2,2V14z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function proibido($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <path d="M0,0h24v24H0V0z" fill="none" />
    </g>
    <g>
        <path
            d="M15.73,3H8.27L3,8.27v7.46L8.27,21h7.46L21,15.73V8.27L15.73,3z M19,14.9L14.9,19H9.1L5,14.9V9.1L9.1,5h5.8L19,9.1V14.9z M14.83,7.76L12,10.59L9.17,7.76L7.76,9.17L10.59,12l-2.83,2.83l1.41,1.41L12,13.41l2.83,2.83l1.41-1.41L13.41,12l2.83-2.83 L14.83,7.76z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function filtrar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <path d="M0,0h24 M24,24H0" fill="none" />
        <path
            d="M7,6h10l-5.01,6.3L7,6z M4.25,5.61C6.27,8.2,10,13,10,13v6c0,0.55,0.45,1,1,1h2c0.55,0,1-0.45,1-1v-6 c0,0,3.72-4.8,5.74-7.39C20.25,4.95,19.78,4,18.95,4H5.04C4.21,4,3.74,4.95,4.25,5.61z" />
        <path d="M0,0h24v24H0V0z" fill="none" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function procurarNaPagina($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zM6 4h7l5 5v8.58l-1.84-1.84c1.28-1.94 1.07-4.57-.64-6.28C14.55 8.49 13.28 8 12 8c-1.28 0-2.55.49-3.53 1.46-1.95 1.95-1.95 5.11 0 7.05.97.97 2.25 1.46 3.53 1.46.96 0 1.92-.28 2.75-.83L17.6 20H6V4zm8.11 11.1c-.56.56-1.31.88-2.11.88s-1.55-.31-2.11-.88c-.56-.56-.88-1.31-.88-2.11s.31-1.55.88-2.11c.56-.57 1.31-.88 2.11-.88s1.55.31 2.11.88c.56.56.88 1.31.88 2.11s-.31 1.55-.88 2.11z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function interrogacao_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5 0-2.21-1.79-4-4-4z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function historico($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.25 2.52.77-1.28-3.52-2.09V8z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function lampada($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0z" fill="none" />
    <path
        d="M9 21c0 .55.45 1 1 1h4c.55 0 1-.45 1-1v-1H9v1zm3-19C8.14 2 5 5.14 5 9c0 2.38 1.19 4.47 3 5.74V17c0 .55.45 1 1 1h6c.55 0 1-.45 1-1v-2.26c1.81-1.27 3-3.36 3-5.74 0-3.86-3.14-7-7-7zm2.85 11.1l-.85.6V16h-4v-2.3l-.85-.6C7.8 12.16 7 10.63 7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.63-.8 3.16-2.15 4.1z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function cadeadoFechado($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <g fill="none">
        <path d="M0 0h24v24H0V0z" />
        <path d="M0 0h24v24H0V0z" opacity=".87" />
    </g>
    <path
        d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6zm9 14H6V10h12v10zm-6-3c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function cadeadoAberto($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6h2c0-1.66 1.34-3 3-3s3 1.34 3 3v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm0 12H6V10h12v10zm-6-3c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function musica($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M12 3l.01 10.55c-.59-.34-1.27-.55-2-.55C7.79 13 6 14.79 6 17s1.79 4 4.01 4S14 19.21 14 17V7h4V3h-6zm-1.99 16c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function adicionarNota($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M13 11h-2v3H8v2h3v3h2v-3h3v-2h-3zm1-9H6c-1.1 0-2 .9-2 2v16c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function sino($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function sino_off($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M160-200v-80h80v-280q0-33 8.5-65t25.5-61l60 60q-7 16-10.5 32.5T320-560v280h248L56-792l56-56 736 736-56 56-146-144H160Zm560-154-80-80v-126q0-66-47-113t-113-47q-26 0-50 8t-44 24l-58-58q20-16 43-28t49-18v-28q0-25 17.5-42.5T480-880q25 0 42.5 17.5T540-820v28q80 20 130 84.5T720-560v206Zm-276-50Zm36 324q-33 0-56.5-23.5T400-160h160q0 33-23.5 56.5T480-80Zm33-481Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function raio_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0z" fill="none" />
    <path
        d="M12 2.02c-5.51 0-9.98 4.47-9.98 9.98s4.47 9.98 9.98 9.98 9.98-4.47 9.98-9.98S17.51 2.02 12 2.02zm0 17.96c-4.4 0-7.98-3.58-7.98-7.98S7.6 4.02 12 4.02 19.98 7.6 19.98 12 16.4 19.98 12 19.98zM12.75 5l-4.5 8.5h3.14V19l4.36-8.5h-3z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function cifrao_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <path
            d="M12,2C6.48,2,2,6.48,2,12s4.48,10,10,10s10-4.48,10-10S17.52,2,12,2z M12,20c-4.41,0-8-3.59-8-8c0-4.41,3.59-8,8-8 s8,3.59,8,8C20,16.41,16.41,20,12,20z M12.89,11.1c-1.78-0.59-2.64-0.96-2.64-1.9c0-1.02,1.11-1.39,1.81-1.39 c1.31,0,1.79,0.99,1.9,1.34l1.58-0.67c-0.15-0.44-0.82-1.91-2.66-2.23V5h-1.75v1.26c-2.6,0.56-2.62,2.85-2.62,2.96 c0,2.27,2.25,2.91,3.35,3.31c1.58,0.56,2.28,1.07,2.28,2.03c0,1.13-1.05,1.61-1.98,1.61c-1.82,0-2.34-1.87-2.4-2.09L8.1,14.75 c0.63,2.19,2.28,2.78,3.02,2.96V19h1.75v-1.24c0.52-0.09,3.02-0.59,3.02-3.22C15.9,13.15,15.29,11.93,12.89,11.1z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function PHP($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <path
            d="M13,9h1.5v6H13v-2.5h-2V15H9.5V9H11v2h2V9z M8,10.5v1C8,12.3,7.3,13,6.5,13h-2v2H3V9h3.5C7.3,9,8,9.7,8,10.5z M6.5,10.5h-2 v1h2V10.5z M21.5,10.5v1c0,0.8-0.7,1.5-1.5,1.5h-2v2h-1.5V9H20C20.8,9,21.5,9.7,21.5,10.5z M20,10.5h-2v1h2V10.5z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function html($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M0-360v-240h60v80h80v-80h60v240h-60v-100H60v100H0Zm310 0v-180h-70v-60h200v60h-70v180h-60Zm170 0v-200q0-17 11.5-28.5T520-600h180q17 0 28.5 11.5T740-560v200h-60v-180h-40v140h-60v-140h-40v180h-60Zm320 0v-240h60v180h100v60H800Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function js($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M300-360q-25 0-42.5-17.5T240-420v-40h60v40h60v-180h60v180q0 25-17.5 42.5T360-360h-60Zm220 0q-17 0-28.5-11.5T480-400v-40h60v20h80v-40H520q-17 0-28.5-11.5T480-500v-60q0-17 11.5-28.5T520-600h120q17 0 28.5 11.5T680-560v40h-60v-20h-80v40h100q17 0 28.5 11.5T680-460v60q0 17-11.5 28.5T640-360H520Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function json($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M190-360h70q17 0 28.5-11.5T300-400v-200h-60v190h-40v-50h-50v60q0 17 11.5 28.5T190-360Zm177 0h60q17 0 28.5-11.5T467-400v-60q0-17-11.5-28.5T427-500h-50v-50h40v20h50v-30q0-17-11.5-28.5T427-600h-60q-17 0-28.5 11.5T327-560v60q0 17 11.5 28.5T367-460h50v50h-40v-20h-50v30q0 17 11.5 28.5T367-360Zm176-60v-120h40v120h-40Zm-10 60h60q17 0 28.5-11.5T633-400v-160q0-17-11.5-28.5T593-600h-60q-17 0-28.5 11.5T493-560v160q0 17 11.5 28.5T533-360Zm127 0h50v-105l40 105h50v-240h-50v105l-40-105h-50v240ZM120-160q-33 0-56.5-23.5T40-240v-480q0-33 23.5-56.5T120-800h720q33 0 56.5 23.5T920-720v480q0 33-23.5 56.5T840-160H120Zm0-80h720v-480H120v480Zm0 0v-480 480Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function css($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M420-360q-17 0-28.5-11.5T380-400v-40h60v20h80v-40H420q-17 0-28.5-11.5T380-500v-60q0-17 11.5-28.5T420-600h120q17 0 28.5 11.5T580-560v40h-60v-20h-80v40h100q17 0 28.5 11.5T580-460v60q0 17-11.5 28.5T540-360H420Zm260 0q-17 0-28.5-11.5T640-400v-40h60v20h80v-40H680q-17 0-28.5-11.5T640-500v-60q0-17 11.5-28.5T680-600h120q17 0 28.5 11.5T840-560v40h-60v-20h-80v40h100q17 0 28.5 11.5T840-460v60q0 17-11.5 28.5T800-360H680Zm-520 0q-17 0-28.5-11.5T120-400v-160q0-17 11.5-28.5T160-600h120q17 0 28.5 11.5T320-560v40h-60v-20h-80v120h80v-20h60v40q0 17-11.5 28.5T280-360H160Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function http($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M40-360v-240h60v80h80v-80h60v240h-60v-100h-80v100H40Zm300 0v-180h-60v-60h180v60h-60v180h-60Zm220 0v-180h-60v-60h180v60h-60v180h-60Zm160 0v-240h140q24 0 42 18t18 42v40q0 24-18 42t-42 18h-80v80h-60Zm60-140h80v-40h-80v40Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function power($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M13 3h-2v10h2V3zm4.83 2.17l-1.42 1.42C17.99 7.86 19 9.81 19 12c0 3.87-3.13 7-7 7s-7-3.13-7-7c0-2.19 1.01-4.14 2.58-5.42L6.17 5.17C4.23 6.82 3 9.26 3 12c0 4.97 4.03 9 9 9s9-4.03 9-9c0-2.74-1.23-5.18-3.17-6.83z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function conversa($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M15 4v7H5.17l-.59.59-.58.58V4h11m1-2H3c-.55 0-1 .45-1 1v14l4-4h10c.55 0 1-.45 1-1V3c0-.55-.45-1-1-1zm5 4h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function sincronizacao($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M.01 0h24v24h-24V0z" fill="none" />
    <path
        d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function wifi($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0zm0 0h24v24H0V0z" fill="none" />
    <path
        d="M1 9l2 2c4.97-4.97 13.03-4.97 18 0l2-2C16.93 2.93 7.08 2.93 1 9zm8 8l3 3 3-3c-1.65-1.66-4.34-1.66-6 0zm-4-4l2 2c2.76-2.76 7.24-2.76 10 0l2-2C15.14 9.14 8.87 9.14 5 13z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function cifrao($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function circulo($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <path
            d="M12,2C6.47,2,2,6.47,2,12c0,5.53,4.47,10,10,10s10-4.47,10-10C22,6.47,17.53,2,12,2z M12,20c-4.42,0-8-3.58-8-8 c0-4.42,3.58-8,8-8s8,3.58,8,8C20,16.42,16.42,20,12,20z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function copyright($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M10.08 10.86c.05-.33.16-.62.3-.87s.34-.46.59-.62c.24-.15.54-.22.91-.23.23.01.44.05.63.13.2.09.38.21.52.36s.25.33.34.53.13.42.14.64h1.79c-.02-.47-.11-.9-.28-1.29s-.4-.73-.7-1.01-.66-.5-1.08-.66-.88-.23-1.39-.23c-.65 0-1.22.11-1.7.34s-.88.53-1.2.92-.56.84-.71 1.36S8 11.29 8 11.87v.27c0 .58.08 1.12.23 1.64s.39.97.71 1.35.72.69 1.2.91c.48.22 1.05.34 1.7.34.47 0 .91-.08 1.32-.23s.77-.36 1.08-.63.56-.58.74-.94.29-.74.3-1.15h-1.79c-.01.21-.06.4-.15.58s-.21.33-.36.46-.32.23-.52.3c-.19.07-.39.09-.6.1-.36-.01-.66-.08-.89-.23-.25-.16-.45-.37-.59-.62s-.25-.55-.3-.88-.08-.67-.08-1v-.27c0-.35.03-.68.08-1.01zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function setasDiminuir($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <rect fill="none" height="24" width="24" />
    <path
        d="M22,3.41l-5.29,5.29L20,12h-8V4l3.29,3.29L20.59,2L22,3.41z M3.41,22l5.29-5.29L12,20v-8H4l3.29,3.29L2,20.59L3.41,22z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function setasAumentar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <rect fill="none" height="24" width="24" />
    <polygon points="21,11 21,3 13,3 16.29,6.29 6.29,16.29 3,13 3,21 11,21 7.71,17.71 17.71,7.71" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function categorias($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg width="32px" height="32px" viewBox="0 0 32 32" id="icon"
    xmlns="http://www.w3.org/2000/svg">
    <defs>
        <style>
        .cls-1 {
            fill: none;
        }
        </style>
    </defs>
    <title>category</title>
    <path
        d="M27,22.1414V18a2,2,0,0,0-2-2H17V12h2a2.0023,2.0023,0,0,0,2-2V4a2.0023,2.0023,0,0,0-2-2H13a2.002,2.002,0,0,0-2,2v6a2.002,2.002,0,0,0,2,2h2v4H7a2,2,0,0,0-2,2v4.1421a4,4,0,1,0,2,0V18h8v4.142a4,4,0,1,0,2,0V18h8v4.1414a4,4,0,1,0,2,0ZM13,4h6l.001,6H13ZM8,26a2,2,0,1,1-2-2A2.0023,2.0023,0,0,1,8,26Zm10,0a2,2,0,1,1-2-2A2.0027,2.0027,0,0,1,18,26Zm8,2a2,2,0,1,1,2-2A2.0023,2.0023,0,0,1,26,28Z" />
    <rect id="_Transparent_Rectangle_" data-name="&lt;Transparent Rectangle&gt;" class="cls-1" width="32" height="32" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function categorias2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M12 2l-5.5 9h11L12 2zm0 3.84L13.93 9h-3.87L12 5.84zM17.5 13c-2.49 0-4.5 2.01-4.5 4.5s2.01 4.5 4.5 4.5 4.5-2.01 4.5-4.5-2.01-4.5-4.5-4.5zm0 7c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5zM3 21.5h8v-8H3v8zm2-6h4v4H5v-4z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function dashboard2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <rect fill="none" height="24" width="24" />
    <path
        d="M19,3H5C3.9,3,3,3.9,3,5v14c0,1.1,0.9,2,2,2h14c1.1,0,2-0.9,2-2V5C21,3.9,20.1,3,19,3z M5,19V5h6v14H5z M19,19h-6v-7h6V19z M19,10h-6V5h6V10z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function paleta($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M2.53 19.65l1.34.56v-9.03l-2.43 5.86c-.41 1.02.08 2.19 1.09 2.61zm19.5-3.7L17.07 3.98c-.31-.75-1.04-1.21-1.81-1.23-.26 0-.53.04-.79.15L7.1 5.95c-.75.31-1.21 1.03-1.23 1.8-.01.27.04.54.15.8l4.96 11.97c.31.76 1.05 1.22 1.83 1.23.26 0 .52-.05.77-.15l7.36-3.05c1.02-.42 1.51-1.59 1.09-2.6zm-9.2 3.8L7.87 7.79l7.35-3.04h.01l4.95 11.95-7.35 3.05z" />
    <circle cx="11" cy="9" r="1" />
    <path d="M5.88 19.75c0 1.1.9 2 2 2h1.45l-3.45-8.34v6.34z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function verificado($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <g>
            <path
                d="M23,11.99l-2.44-2.79l0.34-3.69l-3.61-0.82L15.4,1.5L12,2.96L8.6,1.5L6.71,4.69L3.1,5.5L3.44,9.2L1,11.99l2.44,2.79 l-0.34,3.7l3.61,0.82L8.6,22.5l3.4-1.47l3.4,1.46l1.89-3.19l3.61-0.82l-0.34-3.69L23,11.99z M19.05,13.47l-0.56,0.65l0.08,0.85 l0.18,1.95l-1.9,0.43l-0.84,0.19l-0.44,0.74l-0.99,1.68l-1.78-0.77L12,18.85l-0.79,0.34l-1.78,0.77l-0.99-1.67l-0.44-0.74 l-0.84-0.19l-1.9-0.43l0.18-1.96l0.08-0.85l-0.56-0.65l-1.29-1.47l1.29-1.48l0.56-0.65L5.43,9.01L5.25,7.07l1.9-0.43l0.84-0.19 l0.44-0.74l0.99-1.68l1.78,0.77L12,5.14l0.79-0.34l1.78-0.77l0.99,1.68l0.44,0.74l0.84,0.19l1.9,0.43l-0.18,1.95l-0.08,0.85 l0.56,0.65l1.29,1.47L19.05,13.47z" />
            <polygon points="10.09,13.75 7.77,11.42 6.29,12.91 10.09,16.72 17.43,9.36 15.95,7.87" />
        </g>
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function carro($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.85 7h10.29l1.08 3.11H5.77L6.85 7zM19 17H5v-5h14v5z" />
    <circle cx="7.5" cy="14.5" r="1.5" />
    <circle cx="16.5" cy="14.5" r="1.5" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function sacola($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
        <path
            d="M18,6h-2c0-2.21-1.79-4-4-4S8,3.79,8,6H6C4.9,6,4,6.9,4,8v12c0,1.1,0.9,2,2,2h12c1.1,0,2-0.9,2-2V8C20,6.9,19.1,6,18,6z M12,4c1.1,0,2,0.9,2,2h-4C10,4.9,10.9,4,12,4z M18,20H6V8h2v2c0,0.55,0.45,1,1,1s1-0.45,1-1V8h4v2c0,0.55,0.45,1,1,1s1-0.45,1-1V8 h2V20z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }
    public static function adicionarCarrinho($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M11 9h2V6h3V4h-3V1h-2v3H8v2h3v3zm-4 9c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2zm-8.9-5h7.45c.75 0 1.41-.41 1.75-1.03l3.86-7.01L19.42 4l-3.87 7H8.53L4.27 2H1v2h2l3.6 7.59-1.35 2.44C4.52 15.37 5.48 17 7 17h12v-2H7l1.1-2z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function cancelarCarrinho($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M1.41 1.13L0 2.54l4.39 4.39 2.21 4.66-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h7.46l1.38 1.38c-.5.36-.83.95-.83 1.62 0 1.1.89 2 1.99 2 .67 0 1.26-.33 1.62-.84L21.46 24l1.41-1.41L1.41 1.13zM7 15l1.1-2h2.36l2 2H7zM20 4H7.12l2 2h9.19l-2.76 5h-1.44l1.94 1.94c.54-.14.99-.49 1.25-.97l3.58-6.49C21.25 4.82 20.76 4 20 4zM7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function cifrao_off($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M12.5 6.9c1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-.39.08-.75.21-1.1.36l1.51 1.51c.32-.08.69-.13 1.09-.13zM5.47 3.92L4.06 5.33 7.5 8.77c0 2.08 1.56 3.22 3.91 3.91l3.51 3.51c-.34.49-1.05.91-2.42.91-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c.96-.18 1.83-.55 2.46-1.12l2.22 2.22 1.41-1.41L5.47 3.92z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function dinheiro($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
        <path
            d="M19,14V6c0-1.1-0.9-2-2-2H3C1.9,4,1,4.9,1,6v8c0,1.1,0.9,2,2,2h14C18.1,16,19,15.1,19,14z M17,14H3V6h14V14z M10,7 c-1.66,0-3,1.34-3,3s1.34,3,3,3s3-1.34,3-3S11.66,7,10,7z M23,7v11c0,1.1-0.9,2-2,2H4c0-1,0-0.9,0-2h17V7C22.1,7,22,7,23,7z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function moto($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" fill-rule="evenodd" height="24" width="24" x="0" y="0" />
        <path
            d="M4.17,11L4.17,11C4.12,11,4.06,11,4,11H4.17 M13.41,5H9v2h3.59l2,2H11l-4,2L5,9H0v2h4c-2.21,0-4,1.79-4,4 c0,2.21,1.79,4,4,4c2.21,0,4-1.79,4-4l2,2h3l3.49-6.1l1.01,1.01C16.59,12.64,16,13.75,16,15c0,2.21,1.79,4,4,4c2.21,0,4-1.79,4-4 c0-2.21-1.79-4-4-4c-0.18,0-0.36,0.03-0.53,0.05L17.41,9H20V6l-3.72,1.86L13.41,5L13.41,5z M20,17c-1.1,0-2-0.9-2-2 c0-1.1,0.9-2,2-2c1.1,0,2,0.9,2,2C22,16.1,21.1,17,20,17L20,17z M4,17c-1.1,0-2-0.9-2-2c0-1.1,0.9-2,2-2c1.1,0,2,0.9,2,2 C6,16.1,5.1,17,4,17L4,17z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function grafico($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6h-6z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function imagens($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M2 6H0v5h.01L0 20c0 1.1.9 2 2 2h18v-2H2V6zm5 9h14l-3.5-4.5-2.5 3.01L11.5 9zM22 4h-8l-2-2H6c-1.1 0-1.99.9-1.99 2L4 16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 12H6V4h5.17l1.41 1.41.59.59H22v10z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function relogio($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M14.31 2l.41 2.48C13.87 4.17 12.96 4 12 4c-.95 0-1.87.17-2.71.47L9.7 2h4.61m.41 17.52L14.31 22H9.7l-.41-2.47c.84.3 1.76.47 2.71.47.96 0 1.87-.17 2.72-.48M16 0H8l-.95 5.73C5.19 7.19 4 9.45 4 12s1.19 4.81 3.05 6.27L8 24h8l.96-5.73C18.81 16.81 20 14.54 20 12s-1.19-4.81-3.04-6.27L16 0zm-4 18c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function monitor($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M21 3H3c-1.11 0-2 .89-2 2v12c0 1.1.89 2 2 2h5v2h8v-2h5c1.1 0 1.99-.9 1.99-2L23 5c0-1.11-.9-2-2-2zm0 14H3V5h18v12z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function pizza($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M12 2C8.43 2 5.23 3.54 3.01 6L12 22l8.99-16C18.78 3.55 15.57 2 12 2zm0 15.92L5.51 6.36C7.32 4.85 9.62 4 12 4s4.68.85 6.49 2.36L12 17.92zM9 5.5c-.83 0-1.5.67-1.5 1.5S8.17 8.5 9 8.5s1.5-.67 1.5-1.5S9.82 5.5 9 5.5zm1.5 7.5c0 .83.67 1.5 1.5 1.5.82 0 1.5-.67 1.5-1.5s-.68-1.5-1.5-1.5-1.5.67-1.5 1.5z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function drink($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path d="M14.77 9L12 12.11 9.23 9h5.54M21 3H3v2l8 9v5H6v2h12v-2h-5v-5l8-9V3zM7.43 7L5.66 5h12.69l-1.78 2H7.43z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function bicicleta($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <path
            d="M18.18,10l-1.7-4.68C16.19,4.53,15.44,4,14.6,4H12v2h2.6l1.46,4h-4.81l-0.36-1H12V7H7v2h1.75l1.82,5H9.9 c-0.44-2.23-2.31-3.88-4.65-3.99C2.45,9.87,0,12.2,0,15c0,2.8,2.2,5,5,5c2.46,0,4.45-1.69,4.9-4h4.2c0.44,2.23,2.31,3.88,4.65,3.99 c2.8,0.13,5.25-2.19,5.25-5c0-2.8-2.2-5-5-5H18.18z M7.82,16c-0.4,1.17-1.49,2-2.82,2c-1.68,0-3-1.32-3-3s1.32-3,3-3 c1.33,0,2.42,0.83,2.82,2H5v2H7.82z M14.1,14h-1.4l-0.73-2H15C14.56,12.58,14.24,13.25,14.1,14z M19,18c-1.68,0-3-1.32-3-3 c0-0.93,0.41-1.73,1.05-2.28l0.96,2.64l1.88-0.68l-0.97-2.67c0.03,0,0.06-0.01,0.09-0.01c1.68,0,3,1.32,3,3S20.68,18,19,18z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function pedalandoBicicleta($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M15.5 5.5c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zM5 12c-2.8 0-5 2.2-5 5s2.2 5 5 5 5-2.2 5-5-2.2-5-5-5zm0 8.5c-1.9 0-3.5-1.6-3.5-3.5s1.6-3.5 3.5-3.5 3.5 1.6 3.5 3.5-1.6 3.5-3.5 3.5zm5.8-10l2.4-2.4.8.8c1.3 1.3 3 2.1 5.1 2.1V9c-1.5 0-2.7-.6-3.6-1.5l-1.9-1.9c-.5-.4-1-.6-1.6-.6s-1.1.2-1.4.6L7.8 8.4c-.4.4-.6.9-.6 1.4 0 .6.2 1.1.6 1.4L11 14v5h2v-6.2l-2.2-2.3zM19 12c-2.8 0-5 2.2-5 5s2.2 5 5 5 5-2.2 5-5-2.2-5-5-5zm0 8.5c-1.9 0-3.5-1.6-3.5-3.5s1.6-3.5 3.5-3.5 3.5 1.6 3.5 3.5-1.6 3.5-3.5 3.5z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function camadas($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M11.99 18.54l-7.37-5.73L3 14.07l9 7 9-7-1.63-1.27zM12 16l7.36-5.73L21 9l-9-7-9 7 1.63 1.27L12 16zm0-11.47L17.74 9 12 13.47 6.26 9 12 4.53z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function camadas2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-400 40-640l440-240 440 240-440 240Zm0 160L63-467l84-46 333 182 333-182 84 46-417 227Zm0 160L63-307l84-46 333 182 333-182 84 46L480-80Zm0-411 273-149-273-149-273 149 273 149Zm0-149Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function aviao($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function caminhao($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zm-.5 1.5l1.96 2.5H17V9.5h2.5zM6 18c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm2.22-3c-.55-.61-1.33-1-2.22-1s-1.67.39-2.22 1H3V6h12v9H8.22zM18 18c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function pastas($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <path
            d="M3,19h17v2H3c-1.1,0-2-0.9-2-2V6h2V19z M23,6v9c0,1.1-0.9,2-2,2H7c-1.1,0-2-0.9-2-2L5.01,4C5.01,2.9,5.9,2,7,2h5l2,2h7 C22.1,4,23,4.9,23,6z M7,15h14V6h-7.83l-2-2H7V15z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function abrirArquivo($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <path
            d="M15,22H6c-1.1,0-2-0.9-2-2V4c0-1.1,0.9-2,2-2h8l6,6v6h-2V9h-5V4H6v16h9V22z M19,21.66l0-2.24l2.95,2.95l1.41-1.41L20.41,18 h2.24v-2H17v5.66H19z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function criarPasta($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M20 6h-8l-2-2H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm0 12H4V6h5.17l2 2H20v10zm-8-4h2v2h2v-2h2v-2h-2v-2h-2v2h-2z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function pasta($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M20 6h-8l-2-2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 12H4V8h16v10z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function grid($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <g>
            <g>
                <path
                    d="M3,3v8h8V3H3z M9,9H5V5h4V9z M3,13v8h8v-8H3z M9,19H5v-4h4V19z M13,3v8h8V3H13z M19,9h-4V5h4V9z M13,13v8h8v-8H13z M19,19h-4v-4h4V19z" />
            </g>
        </g>
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function download3($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <path
            d="M18,15v3H6v-3H4v3c0,1.1,0.9,2,2,2h12c1.1,0,2-0.9,2-2v-3H18z M17,11l-1.41-1.41L13,12.17V4h-2v8.17L8.41,9.59L7,11l5,5 L17,11z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function email($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6zm-2 0l-8 5-8-5h16zm0 12H4V8l8 5 8-5v10z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function chat($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M4 4h16v12H5.17L4 17.17V4m0-2c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2H4zm2 10h8v2H6v-2zm0-3h12v2H6V9zm0-3h12v2H6V6z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function chat2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M880-80 720-240H320q-33 0-56.5-23.5T240-320v-40h440q33 0 56.5-23.5T760-440v-280h40q33 0 56.5 23.5T880-640v560ZM160-473l47-47h393v-280H160v327ZM80-280v-520q0-33 23.5-56.5T160-880h440q33 0 56.5 23.5T680-800v280q0 33-23.5 56.5T600-440H240L80-280Zm80-240v-280 280Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function bitcoin($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <path
            d="M17.06,11.57C17.65,10.88,18,9.98,18,9c0-1.86-1.27-3.43-3-3.87L15,3h-2v2h-2V3H9v2H6v2h2v10H6v2h3v2h2v-2h2v2h2v-2 c2.21,0,4-1.79,4-4C19,13.55,18.22,12.27,17.06,11.57z M10,7h4c1.1,0,2,0.9,2,2s-0.9,2-2,2h-4V7z M15,17h-5v-4h5c1.1,0,2,0.9,2,2 S16.1,17,15,17z" />
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function blur_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M10 9c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1zm0 4c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1zM7 9.5c-.28 0-.5.22-.5.5s.22.5.5.5.5-.22.5-.5-.22-.5-.5-.5zm3 7c-.28 0-.5.22-.5.5s.22.5.5.5.5-.22.5-.5-.22-.5-.5-.5zm-3-3c-.28 0-.5.22-.5.5s.22.5.5.5.5-.22.5-.5-.22-.5-.5-.5zm3-6c.28 0 .5-.22.5-.5s-.22-.5-.5-.5-.5.22-.5.5.22.5.5.5zM14 9c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1zm0-1.5c.28 0 .5-.22.5-.5s-.22-.5-.5-.5-.5.22-.5.5.22.5.5.5zm3 6c-.28 0-.5.22-.5.5s.22.5.5.5.5-.22.5-.5-.22-.5-.5-.5zm0-4c-.28 0-.5.22-.5.5s.22.5.5.5.5-.22.5-.5-.22-.5-.5-.5zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm2-3.5c-.28 0-.5.22-.5.5s.22.5.5.5.5-.22.5-.5-.22-.5-.5-.5zm0-3.5c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function blur($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0z" fill="none" />
    <path
        d="M6 13c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1zm0 4c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1zm0-8c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1zm-3 .5c-.28 0-.5.22-.5.5s.22.5.5.5.5-.22.5-.5-.22-.5-.5-.5zM6 5c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1zm15 5.5c.28 0 .5-.22.5-.5s-.22-.5-.5-.5-.5.22-.5.5.22.5.5.5zM14 7c.55 0 1-.45 1-1s-.45-1-1-1-1 .45-1 1 .45 1 1 1zm0-3.5c.28 0 .5-.22.5-.5s-.22-.5-.5-.5-.5.22-.5.5.22.5.5.5zm-11 10c-.28 0-.5.22-.5.5s.22.5.5.5.5-.22.5-.5-.22-.5-.5-.5zm7 7c-.28 0-.5.22-.5.5s.22.5.5.5.5-.22.5-.5-.22-.5-.5-.5zm0-17c.28 0 .5-.22.5-.5s-.22-.5-.5-.5-.5.22-.5.5.22.5.5.5zM10 7c.55 0 1-.45 1-1s-.45-1-1-1-1 .45-1 1 .45 1 1 1zm0 5.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm8 .5c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1zm0 4c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1zm0-8c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1zm0-4c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1zm3 8.5c-.28 0-.5.22-.5.5s.22.5.5.5.5-.22.5-.5-.22-.5-.5-.5zM14 17c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1zm0 3.5c-.28 0-.5.22-.5.5s.22.5.5.5.5-.22.5-.5-.22-.5-.5-.5zm-4-12c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0 8.5c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1zm4-4.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0-4c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function fotos($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M15.96 10.29l-2.75 3.54-1.96-2.36L8.5 15h11l-3.54-4.71zM3 5H1v16c0 1.1.9 2 2 2h16v-2H3V5zm18-4H7c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V3c0-1.1-.9-2-2-2zm0 16H7V3h14v14z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function mosaico($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px"
    viewBox="0 0 24 24" width="24px" fill="#000000">
    <g>
        <rect fill="none" height="24" width="24" />
    </g>
    <g>
        <g>
            <path d="M3,5v14c0,1.1,0.89,2,2,2h6V3H5C3.89,3,3,3.9,3,5z M9,19H5V5h4V19z" />
            <path d="M19,3h-6v8h8V5C21,3.9,20.1,3,19,3z M19,9h-4V5h4V9z" />
            <path d="M13,21h6c1.1,0,2-0.9,2-2v-6h-8V21z M15,15h4v4h-4V15z" />
        </g>
    </g>
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function brilho($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M20 8.69V4h-4.69L12 .69 8.69 4H4v4.69L.69 12 4 15.31V20h4.69L12 23.31 15.31 20H20v-4.69L23.31 12 20 8.69zm-2 5.79V18h-3.52L12 20.48 9.52 18H6v-3.52L3.52 12 6 9.52V6h3.52L12 3.52 14.48 6H18v3.52L20.48 12 18 14.48zM12.29 7c-.74 0-1.45.17-2.08.46 1.72.79 2.92 2.53 2.92 4.54s-1.2 3.75-2.92 4.54c.63.29 1.34.46 2.08.46 2.76 0 5-2.24 5-5s-2.24-5-5-5z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function grid2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M20 2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM8 20H4v-4h4v4zm0-6H4v-4h4v4zm0-6H4V4h4v4zm6 12h-4v-4h4v4zm0-6h-4v-4h4v4zm0-6h-4V4h4v4zm6 12h-4v-4h4v4zm0-6h-4v-4h4v4zm0-6h-4V4h4v4z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function cores($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0z" fill="none" />
    <path
        d="M12 22C6.49 22 2 17.51 2 12S6.49 2 12 2s10 4.04 10 9c0 3.31-2.69 6-6 6h-1.77c-.28 0-.5.22-.5.5 0 .12.05.23.13.33.41.47.64 1.06.64 1.67 0 1.38-1.12 2.5-2.5 2.5zm0-18c-4.41 0-8 3.59-8 8s3.59 8 8 8c.28 0 .5-.22.5-.5 0-.16-.08-.28-.14-.35-.41-.46-.63-1.05-.63-1.65 0-1.38 1.12-2.5 2.5-2.5H16c2.21 0 4-1.79 4-4 0-3.86-3.59-7-8-7z" />
    <circle cx="6.5" cy="11.5" r="1.5" />
    <circle cx="9.5" cy="7.5" r="1.5" />
    <circle cx="14.5" cy="7.5" r="1.5" />
    <circle cx="17.5" cy="11.5" r="1.5" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function pincel($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M7 16c.55 0 1 .45 1 1 0 1.1-.9 2-2 2-.17 0-.33-.02-.5-.05.31-.55.5-1.21.5-1.95 0-.55.45-1 1-1M18.67 3c-.26 0-.51.1-.71.29L9 12.25 11.75 15l8.96-8.96c.39-.39.39-1.02 0-1.41l-1.34-1.34c-.2-.2-.45-.29-.7-.29zM7 14c-1.66 0-3 1.34-3 3 0 1.31-1.16 2-2 2 .92 1.22 2.49 2 4 2 2.21 0 4-1.79 4-4 0-1.66-1.34-3-3-3z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function sol($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
    fill="#000000">
    <path d="M0 0h24v24H0V0z" fill="none" />
    <path
        d="M6.76 4.84l-1.8-1.79-1.41 1.41 1.79 1.79zM1 10.5h3v2H1zM11 .55h2V3.5h-2zm8.04 2.495l1.408 1.407-1.79 1.79-1.407-1.408zm-1.8 15.115l1.79 1.8 1.41-1.41-1.8-1.79zM20 10.5h3v2h-3zm-8-5c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm-1 4h2v2.95h-2zm-7.45-.96l1.41 1.41 1.79-1.8-1.41-1.41z" />
</svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function youtube($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg height="25px" width="25px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
            viewBox="0 0 461.001 461.001" xml:space="preserve">
            <g>
                <path style="fill:#F61C0D;" d="M365.257,67.393H95.744C42.866,67.393,0,110.259,0,163.137v134.728
                    c0,52.878,42.866,95.744,95.744,95.744h269.513c52.878,0,95.744-42.866,95.744-95.744V163.137
                    C461.001,110.259,418.135,67.393,365.257,67.393z M300.506,237.056l-126.06,60.123c-3.359,1.602-7.239-0.847-7.239-4.568V168.607
                    c0-3.774,3.982-6.22,7.348-4.514l126.06,63.881C304.363,229.873,304.298,235.248,300.506,237.056z"/>
            </g>
        </svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function salvar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm2 16H5V5h11.17L19 7.83V19zm-7-7c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3zM6 6h9v4H6z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function filtrar2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function filtrar_off($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><g><rect fill="none" height="24" width="24"/></g><g><g><path d="M16.95,6l-3.57,4.55l1.43,1.43c1.03-1.31,4.98-6.37,4.98-6.37C20.3,4.95,19.83,4,19,4H6.83l2,2H16.95z"/><path d="M2.81,2.81L1.39,4.22L10,13v6c0,0.55,0.45,1,1,1h2c0.55,0,1-0.45,1-1v-2.17l5.78,5.78l1.41-1.41L2.81,2.81z"/></g></g></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function codigo($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M9.4 16.6L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4zm5.2 0l4.6-4.6-4.6-4.6L16 6l6 6-6 6-1.4-1.4z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function touch($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><g><rect fill="none" height="24" width="24"/></g><g><g><path d="M18.19,12.44l-3.24-1.62c1.29-1,2.12-2.56,2.12-4.32c0-3.03-2.47-5.5-5.5-5.5s-5.5,2.47-5.5,5.5c0,2.13,1.22,3.98,3,4.89 v3.26c-2.15-0.46-2.02-0.44-2.26-0.44c-0.53,0-1.03,0.21-1.41,0.59L4,16.22l5.09,5.09C9.52,21.75,10.12,22,10.74,22h6.3 c0.98,0,1.81-0.7,1.97-1.67l0.8-4.71C20.03,14.32,19.38,13.04,18.19,12.44z M17.84,15.29L17.04,20h-6.3 c-0.09,0-0.17-0.04-0.24-0.1l-3.68-3.68l4.25,0.89V6.5c0-0.28,0.22-0.5,0.5-0.5c0.28,0,0.5,0.22,0.5,0.5v6h1.76l3.46,1.73 C17.69,14.43,17.91,14.86,17.84,15.29z M8.07,6.5c0-1.93,1.57-3.5,3.5-3.5s3.5,1.57,3.5,3.5c0,0.95-0.38,1.81-1,2.44V6.5 c0-1.38-1.12-2.5-2.5-2.5c-1.38,0-2.5,1.12-2.5,2.5v2.44C8.45,8.31,8.07,7.45,8.07,6.5z"/></g></g></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function bussola($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-5.5-2.5l7.51-3.49L17.5 6.5 9.99 9.99 6.5 17.5zm5.5-6.6c.61 0 1.1.49 1.1 1.1s-.49 1.1-1.1 1.1-1.1-.49-1.1-1.1.49-1.1 1.1-1.1z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function az($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M14.94 4.66h-4.72l2.36-2.36 2.36 2.36zm-4.69 14.71h4.66l-2.33 2.33-2.33-2.33zM6.1 6.27L1.6 17.73h1.84l.92-2.45h5.11l.92 2.45h1.84L7.74 6.27H6.1zm-1.13 7.37l1.94-5.18 1.94 5.18H4.97zm10.76 2.5h6.12v1.59h-8.53v-1.29l5.92-8.56h-5.88v-1.6h8.3v1.26l-5.93 8.6z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function setasCimaBaixo($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 5.83L15.17 9l1.41-1.41L12 3 7.41 7.59 8.83 9 12 5.83zm0 12.34L8.83 15l-1.41 1.41L12 21l4.59-4.59L15.17 15 12 18.17z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function lista4($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function imagem_off($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><g><rect fill="none" height="24" width="24"/></g><g><g><path d="M19,5v11.17l2,2V5c0-1.1-0.9-2-2-2H5.83l2,2H19z"/><path d="M2.81,2.81L1.39,4.22L3,5.83V19c0,1.1,0.9,2,2,2h13.17l1.61,1.61l1.41-1.41L2.81,2.81z M5,19V7.83l7.07,7.07L11.25,16 L9,13l-3,4h8.17l2,2H5z"/></g></g></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function usuarios($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><rect fill="none" height="24" width="24"/><g><path d="M4,13c1.1,0,2-0.9,2-2c0-1.1-0.9-2-2-2s-2,0.9-2,2C2,12.1,2.9,13,4,13z M5.13,14.1C4.76,14.04,4.39,14,4,14 c-0.99,0-1.93,0.21-2.78,0.58C0.48,14.9,0,15.62,0,16.43V18l4.5,0v-1.61C4.5,15.56,4.73,14.78,5.13,14.1z M20,13c1.1,0,2-0.9,2-2 c0-1.1-0.9-2-2-2s-2,0.9-2,2C18,12.1,18.9,13,20,13z M24,16.43c0-0.81-0.48-1.53-1.22-1.85C21.93,14.21,20.99,14,20,14 c-0.39,0-0.76,0.04-1.13,0.1c0.4,0.68,0.63,1.46,0.63,2.29V18l4.5,0V16.43z M16.24,13.65c-1.17-0.52-2.61-0.9-4.24-0.9 c-1.63,0-3.07,0.39-4.24,0.9C6.68,14.13,6,15.21,6,16.39V18h12v-1.61C18,15.21,17.32,14.13,16.24,13.65z M8.07,16 c0.09-0.23,0.13-0.39,0.91-0.69c0.97-0.38,1.99-0.56,3.02-0.56s2.05,0.18,3.02,0.56c0.77,0.3,0.81,0.46,0.91,0.69H8.07z M12,8 c0.55,0,1,0.45,1,1s-0.45,1-1,1s-1-0.45-1-1S11.45,8,12,8 M12,6c-1.66,0-3,1.34-3,3c0,1.66,1.34,3,3,3s3-1.34,3-3 C15,7.34,13.66,6,12,6L12,6z"/></g></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function adicionar_usuario($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><g><rect fill="none" height="24" width="24"/></g><g><path d="M20,9V6h-2v3h-3v2h3v3h2v-3h3V9H20z M9,12c2.21,0,4-1.79,4-4c0-2.21-1.79-4-4-4S5,5.79,5,8C5,10.21,6.79,12,9,12z M9,6 c1.1,0,2,0.9,2,2c0,1.1-0.9,2-2,2S7,9.1,7,8C7,6.9,7.9,6,9,6z M15.39,14.56C13.71,13.7,11.53,13,9,13c-2.53,0-4.71,0.7-6.39,1.56 C1.61,15.07,1,16.1,1,17.22V20h16v-2.78C17,16.1,16.39,15.07,15.39,14.56z M15,18H3v-0.78c0-0.38,0.2-0.72,0.52-0.88 C4.71,15.73,6.63,15,9,15c2.37,0,4.29,0.73,5.48,1.34C14.8,16.5,15,16.84,15,17.22V18z"/></g></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function remover_usuario($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><g><rect fill="none" height="24" width="24"/></g><g><g><path d="M14,8c0-2.21-1.79-4-4-4C7.79,4,6,5.79,6,8c0,2.21,1.79,4,4,4C12.21,12,14,10.21,14,8z M12,8c0,1.1-0.9,2-2,2 c-1.1,0-2-0.9-2-2s0.9-2,2-2C11.1,6,12,6.9,12,8z"/><path d="M2,18v2h16v-2c0-2.66-5.33-4-8-4C7.33,14,2,15.34,2,18z M4,18c0.2-0.71,3.3-2,6-2c2.69,0,5.77,1.28,6,2H4z"/><rect height="2" width="6" x="17" y="10"/></g></g></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function usuario_off($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><rect fill="none" height="24" width="24"/><path d="M20,17.17l-3.37-3.38c0.64,0.22,1.23,0.48,1.77,0.76C19.37,15.06,19.98,16.07,20,17.17z M21.19,21.19l-1.41,1.41L17.17,20H4 v-2.78c0-1.12,0.61-2.15,1.61-2.66c1.29-0.66,2.87-1.22,4.67-1.45L1.39,4.22l1.41-1.41L21.19,21.19z M15.17,18l-3-3 c-0.06,0-0.11,0-0.17,0c-2.37,0-4.29,0.73-5.48,1.34C6.2,16.5,6,16.84,6,17.22V18H15.17z M12,6c1.1,0,2,0.9,2,2 c0,0.86-0.54,1.59-1.3,1.87l1.48,1.48C15.28,10.64,16,9.4,16,8c0-2.21-1.79-4-4-4c-1.4,0-2.64,0.72-3.35,1.82l1.48,1.48 C10.41,6.54,11.14,6,12,6z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function telefone($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M6.54 5c.06.89.21 1.76.45 2.59l-1.2 1.2c-.41-1.2-.67-2.47-.76-3.79h1.51m9.86 12.02c.85.24 1.72.39 2.6.45v1.49c-1.32-.09-2.59-.35-3.8-.75l1.2-1.19M7.5 3H4c-.55 0-1 .45-1 1 0 9.39 7.61 17 17 17 .55 0 1-.45 1-1v-3.49c0-.55-.45-1-1-1-1.24 0-2.45-.2-3.57-.57-.1-.04-.21-.05-.31-.05-.26 0-.51.1-.71.29l-2.2 2.2c-2.83-1.45-5.15-3.76-6.59-6.59l2.2-2.2c.28-.28.36-.67.25-1.02C8.7 6.45 8.5 5.25 8.5 4c0-.55-.45-1-1-1z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function telefone2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M162-120q-18 0-30-12t-12-30v-162q0-13 9-23.5t23-14.5l138-28q14-2 28.5 2.5T342-374l94 94q38-22 72-48.5t65-57.5q33-32 60.5-66.5T681-524l-97-98q-8-8-11-19t-1-27l26-140q2-13 13-22.5t25-9.5h162q18 0 30 12t12 30q0 125-54.5 247T631-329Q531-229 409-174.5T162-120Zm556-480q17-39 26-79t14-81h-88l-18 94 66 66ZM360-244l-66-66-94 20v88q41-3 81-14t79-28Zm358-356ZM360-244Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function instagram($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg fill="#000000" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" width="24px" height="24px">    <path d="M 8 3 C 5.243 3 3 5.243 3 8 L 3 16 C 3 18.757 5.243 21 8 21 L 16 21 C 18.757 21 21 18.757 21 16 L 21 8 C 21 5.243 18.757 3 16 3 L 8 3 z M 8 5 L 16 5 C 17.654 5 19 6.346 19 8 L 19 16 C 19 17.654 17.654 19 16 19 L 8 19 C 6.346 19 5 17.654 5 16 L 5 8 C 5 6.346 6.346 5 8 5 z M 17 6 A 1 1 0 0 0 16 7 A 1 1 0 0 0 17 8 A 1 1 0 0 0 18 7 A 1 1 0 0 0 17 6 z M 12 7 C 9.243 7 7 9.243 7 12 C 7 14.757 9.243 17 12 17 C 14.757 17 17 14.757 17 12 C 17 9.243 14.757 7 12 7 z M 12 9 C 13.654 9 15 10.346 15 12 C 15 13.654 13.654 15 12 15 C 10.346 15 9 13.654 9 12 C 9 10.346 10.346 9 12 9 z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function relogio2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function arquivar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m480-240 160-160-56-56-64 64v-168h-80v168l-64-64-56 56 160 160ZM200-640v440h560v-440H200Zm0 520q-33 0-56.5-23.5T120-200v-499q0-14 4.5-27t13.5-24l50-61q11-14 27.5-21.5T250-840h460q18 0 34.5 7.5T772-811l50 61q9 11 13.5 24t4.5 27v499q0 33-23.5 56.5T760-120H200Zm16-600h528l-34-40H250l-34 40Zm264 300Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function play($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M320-200v-560l440 280-440 280Zm80-280Zm0 134 210-134-210-134v268Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function play_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m380-300 280-180-280-180v360ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function pausar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M520-200v-560h240v560H520Zm-320 0v-560h240v560H200Zm400-80h80v-400h-80v400Zm-320 0h80v-400h-80v400Zm0-400v400-400Zm320 0v400-400Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function pausar_c($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M360-320h80v-320h-80v320Zm160 0h80v-320h-80v320ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function microfone($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-400q-50 0-85-35t-35-85v-240q0-50 35-85t85-35q50 0 85 35t35 85v240q0 50-35 85t-85 35Zm0-240Zm-40 520v-123q-104-14-172-93t-68-184h80q0 83 58.5 141.5T480-320q83 0 141.5-58.5T680-520h80q0 105-68 184t-172 93v123h-80Zm40-360q17 0 28.5-11.5T520-520v-240q0-17-11.5-28.5T480-800q-17 0-28.5 11.5T440-760v240q0 17 11.5 28.5T480-480Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function volumeMais($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M560-131v-82q90-26 145-100t55-168q0-94-55-168T560-749v-82q124 28 202 125.5T840-481q0 127-78 224.5T560-131ZM120-360v-240h160l200-200v640L280-360H120Zm440 40v-322q47 22 73.5 66t26.5 96q0 51-26.5 94.5T560-320ZM400-606l-86 86H200v80h114l86 86v-252ZM300-480Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function volumeMenos($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M200-360v-240h160l200-200v640L360-360H200Zm440 40v-322q45 21 72.5 65t27.5 97q0 53-27.5 96T640-320ZM480-606l-86 86H280v80h114l86 86v-252ZM380-480Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function mutar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M792-56 671-177q-25 16-53 27.5T560-131v-82q14-5 27.5-10t25.5-12L480-368v208L280-360H120v-240h128L56-792l56-56 736 736-56 56Zm-8-232-58-58q17-31 25.5-65t8.5-70q0-94-55-168T560-749v-82q124 28 202 125.5T840-481q0 53-14.5 102T784-288ZM650-422l-90-90v-130q47 22 73.5 66t26.5 96q0 15-2.5 29.5T650-422ZM480-592 376-696l104-104v208Zm-80 238v-94l-72-72H200v80h114l86 86Zm-36-130Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function replay($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-80q-75 0-140.5-28.5t-114-77q-48.5-48.5-77-114T120-440h80q0 117 81.5 198.5T480-160q117 0 198.5-81.5T760-440q0-117-81.5-198.5T480-720h-6l62 62-56 58-160-160 160-160 56 58-62 62h6q75 0 140.5 28.5t114 77q48.5 48.5 77 114T840-440q0 75-28.5 140.5t-77 114q-48.5 48.5-114 77T480-80Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function repetir($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M280-80 120-240l160-160 56 58-62 62h406v-160h80v240H274l62 62-56 58Zm-80-440v-240h486l-62-62 56-58 160 160-160 160-56-58 62-62H280v160h-80Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function quadrado($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M320-640v320-320Zm-80 400v-480h480v480H240Zm80-80h320v-320H320v320Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function correr($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M520-40v-240l-84-80-40 176-276-56 16-80 192 40 64-324-72 28v136h-80v-188l158-68q35-15 51.5-19.5T480-720q21 0 39 11t29 29l40 64q26 42 70.5 69T760-520v80q-66 0-123.5-27.5T540-540l-24 120 84 80v300h-80Zm20-700q-33 0-56.5-23.5T460-820q0-33 23.5-56.5T540-900q33 0 56.5 23.5T620-820q0 33-23.5 56.5T540-740Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function caminhar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m280-40 112-564-72 28v136h-80v-188l202-86q14-6 29.5-7t29.5 4q14 5 26.5 14t20.5 23l40 64q26 42 70.5 69T760-520v80q-70 0-125-29t-94-74l-25 123 84 80v300h-80v-260l-84-64-72 324h-84Zm260-700q-33 0-56.5-23.5T460-820q0-33 23.5-56.5T540-900q33 0 56.5 23.5T620-820q0 33-23.5 56.5T540-740Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function engenheiro($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M42-120v-112q0-33 17-62t47-44q51-26 115-44t141-18q77 0 141 18t115 44q30 15 47 44t17 62v112H42Zm80-80h480v-32q0-11-5.5-20T582-266q-36-18-92.5-36T362-320q-71 0-127.5 18T142-266q-9 5-14.5 14t-5.5 20v32Zm240-240q-66 0-113-47t-47-113h-10q-9 0-14.5-5.5T172-620q0-9 5.5-14.5T192-640h10q0-45 22-81t58-57v38q0 9 5.5 14.5T302-720q9 0 14.5-5.5T322-740v-54q9-3 19-4.5t21-1.5q11 0 21 1.5t19 4.5v54q0 9 5.5 14.5T422-720q9 0 14.5-5.5T442-740v-38q36 21 58 57t22 81h10q9 0 14.5 5.5T552-620q0 9-5.5 14.5T532-600h-10q0 66-47 113t-113 47Zm0-80q33 0 56.5-23.5T442-600H282q0 33 23.5 56.5T362-520Zm300 160-6-30q-6-2-11.5-4.5T634-402l-28 10-20-36 22-20v-24l-22-20 20-36 28 10q4-4 10-7t12-5l6-30h40l6 30q6 2 12 5t10 7l28-10 20 36-22 20v24l22 20-20 36-28-10q-5 5-10.5 7.5T708-390l-6 30h-40Zm20-70q12 0 21-9t9-21q0-12-9-21t-21-9q-12 0-21 9t-9 21q0 12 9 21t21 9Zm72-130-8-42q-9-3-16.5-7.5T716-620l-42 14-28-48 34-30q-2-5-2-8v-16q0-3 2-8l-34-30 28-48 42 14q6-6 13.5-10.5T746-798l8-42h56l8 42q9 3 16.5 7.5T848-780l42-14 28 48-34 30q2 5 2 8v16q0 3-2 8l34 30-28 48-42-14q-6 6-13.5 10.5T818-602l-8 42h-56Zm28-90q21 0 35.5-14.5T832-700q0-21-14.5-35.5T782-750q-21 0-35.5 14.5T732-700q0 21 14.5 35.5T782-650ZM362-200Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function compasso($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m270-120-10-88 114-314q15 14 32.5 23.5T444-484L334-182l-64 62Zm420 0-64-62-110-302q20-5 37.5-14.5T586-522l114 314-10 88ZM480-520q-50 0-85-35t-35-85q0-39 22.5-69.5T440-752v-88h80v88q35 12 57.5 42.5T600-640q0 50-35 85t-85 35Zm0-80q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function bolaBasquete($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M162-520h114q-6-38-23-71t-43-59q-18 29-30.5 61.5T162-520Zm522 0h114q-5-36-17.5-68.5T750-650q-26 26-43 59t-23 71ZM210-310q26-26 43-59t23-71H162q5 36 17.5 68.5T210-310Zm540 0q18-29 30.5-61.5T798-440H684q6 38 23 71t43 59ZM358-520h82v-278q-53 8-98.5 29.5T260-712q39 38 64.5 86.5T358-520Zm162 0h82q8-57 33.5-105.5T700-712q-36-35-81.5-56.5T520-798v278Zm-80 358v-278h-82q-8 57-33.5 105.5T260-248q36 35 81.5 56.5T440-162Zm80 0q53-8 98.5-29.5T700-248q-39-38-64.5-86.5T602-440h-82v278Zm-40-318Zm0 400q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function controle($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M182-200q-51 0-79-35.5T82-322l42-300q9-60 53.5-99T282-760h396q60 0 104.5 39t53.5 99l42 300q7 51-21 86.5T778-200q-21 0-39-7.5T706-230l-90-90H344l-90 90q-15 15-33 22.5t-39 7.5Zm16-86 114-114h336l114 114q2 2 16 6 11 0 17.5-6.5T800-304l-44-308q-4-29-26-48.5T678-680H282q-30 0-52 19.5T204-612l-44 308q-2 11 4.5 17.5T182-280q2 0 16-6Zm482-154q17 0 28.5-11.5T720-480q0-17-11.5-28.5T680-520q-17 0-28.5 11.5T640-480q0 17 11.5 28.5T680-440Zm-80-120q17 0 28.5-11.5T640-600q0-17-11.5-28.5T600-640q-17 0-28.5 11.5T560-600q0 17 11.5 28.5T600-560ZM310-440h60v-70h70v-60h-70v-70h-60v70h-70v60h70v70Zm170-40Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function controle2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M160-240q-33 0-56.5-23.5T80-320v-320q0-33 23.5-56.5T160-720h640q33 0 56.5 23.5T880-640v320q0 33-23.5 56.5T800-240H160Zm0-80h640v-320H160v320Zm120-40h80v-80h80v-80h-80v-80h-80v80h-80v80h80v80Zm300 0q25 0 42.5-17.5T640-420q0-25-17.5-42.5T580-480q-25 0-42.5 17.5T520-420q0 25 17.5 42.5T580-360Zm120-120q25 0 42.5-17.5T760-540q0-25-17.5-42.5T700-600q-25 0-42.5 17.5T640-540q0 25 17.5 42.5T700-480ZM160-320v-320 320Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function joystick($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m272-440 208 120 208-120-168-97v137h-80v-137l-168 97Zm168-189v-17q-44-13-72-49.5T340-780q0-58 41-99t99-41q58 0 99 41t41 99q0 48-28 84.5T520-646v17l280 161q19 11 29.5 29.5T840-398v76q0 22-10.5 40.5T800-252L520-91q-19 11-40 11t-40-11L160-252q-19-11-29.5-29.5T120-322v-76q0-22 10.5-40.5T160-468l280-161Zm0 378L200-389v67l280 162 280-162v-67L520-251q-19 11-40 11t-40-11Zm40-469q25 0 42.5-17.5T540-780q0-25-17.5-42.5T480-840q-25 0-42.5 17.5T420-780q0 25 17.5 42.5T480-720Zm0 560Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function controleSetas($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-654Zm174 174Zm-348 0Zm174 174Zm0-234L360-660v-220h240v220L480-540Zm180 180L540-480l120-120h220v240H660Zm-580 0v-240h220l120 120-120 120H80ZM360-80v-220l120-120 120 120v220H360Zm120-574 40-40v-106h-80v106l40 40ZM160-440h106l40-40-40-40H160v80Zm280 280h80v-106l-40-40-40 40v106Zm254-280h106v-80H694l-40 40 40 40Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function vento($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M460-160q-50 0-85-35t-35-85h80q0 17 11.5 28.5T460-240q17 0 28.5-11.5T500-280q0-17-11.5-28.5T460-320H80v-80h380q50 0 85 35t35 85q0 50-35 85t-85 35ZM80-560v-80h540q26 0 43-17t17-43q0-26-17-43t-43-17q-26 0-43 17t-17 43h-80q0-59 40.5-99.5T620-840q59 0 99.5 40.5T760-700q0 59-40.5 99.5T620-560H80Zm660 320v-80q26 0 43-17t17-43q0-26-17-43t-43-17H80v-80h660q59 0 99.5 40.5T880-380q0 59-40.5 99.5T740-240Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function onda($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M80-146v-78q29 0 49.5-9t41.5-19.5q21-10.5 46.5-19T280-280q38 0 62.5 8.5t45.5 19q21 10.5 42 19.5t50 9q29 0 50-9t42-19.5q21-10.5 46-19t62-8.5q38 0 63 8.5t46 19q21 10.5 42 19.5t49 9v78q-38 0-63.5-9T770-174.5q-21-10.5-41-19t-49-8.5q-28 0-48.5 8.5t-41 19Q570-164 544.5-155t-64.5 9q-39 0-64.5-9t-46-19.5Q349-185 329-193.5t-49-8.5q-28 0-48.5 8.5t-41.5 19Q169-164 143.5-155T80-146Zm0-178v-78q29 0 49.5-9t41.5-19.5q21-10.5 46.5-19T280-458q38 0 62.5 8.5t45.5 19q21 10.5 42 19.5t50 9q29 0 50-9t42-19.5q21-10.5 46-19t62-8.5q38 0 63 8.5t46 19q21 10.5 42 19.5t49 9v78q-38 0-63.5-9T770-352.5q-21-10.5-41-19t-49-8.5q-29 0-49.5 8.5t-41 19Q569-342 544-333t-64 9q-39 0-64.5-9t-46-19.5Q349-363 329-371.5t-49-8.5q-28 0-48.5 8.5t-41.5 19Q169-342 143.5-333T80-324Zm0-178v-78q29 0 49.5-9t41.5-19.5q21-10.5 46.5-19T280-636q38 0 62.5 8.5t45.5 19q21 10.5 42 19.5t50 9q29 0 50-9t42-19.5q21-10.5 46-19t62-8.5q38 0 63 8.5t46 19q21 10.5 42 19.5t49 9v78q-38 0-63.5-9T770-530.5q-21-10.5-41-19t-49-8.5q-28 0-48.5 8.5t-41 19Q570-520 544.5-511t-64.5 9q-39 0-64.5-9t-46-19.5Q349-541 329-549.5t-49-8.5q-28 0-48.5 8.5t-41.5 19Q169-520 143.5-511T80-502Zm0-178v-78q29 0 49.5-9t41.5-19.5q21-10.5 46.5-19T280-814q38 0 62.5 8.5t45.5 19q21 10.5 42 19.5t50 9q29 0 50-9t42-19.5q21-10.5 46-19t62-8.5q38 0 63 8.5t46 19q21 10.5 42 19.5t49 9v78q-38 0-63.5-9T770-708.5q-21-10.5-41-19t-49-8.5q-28 0-48.5 8.5t-41 19Q570-698 544.5-689t-64.5 9q-39 0-64.5-9t-46-19.5Q349-719 329-727.5t-49-8.5q-28 0-48.5 8.5t-41.5 19Q169-698 143.5-689T80-680Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function quebraCabeca($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M120-272q0-16 10.5-27t25.5-11q8 0 15.5 2.5T186-300q13 8 26 14t28 6q33 0 56.5-23.5T320-360q0-33-23.5-56.5T240-440q-15 0-29 5t-25 15q-6 5-14 7.5t-16 2.5q-15 0-25.5-11T120-448v-152q0-17 11.5-28.5T160-640h150q-5-15-7.5-30t-2.5-30q0-75 52.5-127.5T480-880q75 0 127.5 52.5T660-700q0 15-2.5 30t-7.5 30h150q17 0 28.5 11.5T840-600v152q0 17-11.5 28.5T800-408q-8 0-14-3.5t-12-8.5q-11-10-25-15t-29-5q-33 0-56.5 23.5T640-360q0 33 23.5 56.5T720-280q15 0 29-5t25-15q5-5 11.5-8.5T800-312q17 0 28.5 11.5T840-272v152q0 17-11.5 28.5T800-80H160q-17 0-28.5-11.5T120-120v-152Zm80 112h560v-46q-10 3-19.5 4.5T720-200q-66 0-113-47t-47-113q0-66 47-113t113-47q11 0 20.5 1.5T760-514v-46H578q-17 0-28.5-11T538-598q0-8 2.5-16.5T550-628q17-12 23.5-31.5T580-700q0-42-29-71t-71-29q-42 0-71 29t-29 71q0 21 6.5 40.5T410-628q7 5 9.5 12.5T422-600q0 17-11.5 28.5T382-560H200v46q10-3 19.5-4.5T240-520q66 0 113 47t47 113q0 66-47 113t-113 47q-11 0-20.5-1.5T200-206v46Zm280-320Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function contas($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M560-520q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35ZM320-330q45-53 108-81.5T560-440q69 0 132 28.5T800-330v-470H320v470Zm0 90q-33 0-56.5-23.5T240-320v-480q0-33 23.5-56.5T320-880h480q33 0 56.5 23.5T880-800v480q0 33-23.5 56.5T800-240H320ZM160-80q-33 0-56.5-23.5T80-160v-560h80v560h560v80H160Zm400-520q-17 0-28.5-11.5T520-640q0-17 11.5-28.5T560-680q17 0 28.5 11.5T600-640q0 17-11.5 28.5T560-600ZM428-320h264q-29-20-63-30t-69-10q-35 0-69 10t-63 30Zm132-245Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function presente($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M160-80v-440H80v-240h208q-5-9-6.5-19t-1.5-21q0-50 35-85t85-35q23 0 43 8.5t37 23.5q17-16 37-24t43-8q50 0 85 35t35 85q0 11-2 20.5t-6 19.5h208v240h-80v440H160Zm400-760q-17 0-28.5 11.5T520-800q0 17 11.5 28.5T560-760q17 0 28.5-11.5T600-800q0-17-11.5-28.5T560-840Zm-200 40q0 17 11.5 28.5T400-760q17 0 28.5-11.5T440-800q0-17-11.5-28.5T400-840q-17 0-28.5 11.5T360-800ZM160-680v80h280v-80H160Zm280 520v-360H240v360h200Zm80 0h200v-360H520v360Zm280-440v-80H520v80h280Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function robo($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M160-360q-50 0-85-35t-35-85q0-50 35-85t85-35v-80q0-33 23.5-56.5T240-760h120q0-50 35-85t85-35q50 0 85 35t35 85h120q33 0 56.5 23.5T800-680v80q50 0 85 35t35 85q0 50-35 85t-85 35v160q0 33-23.5 56.5T720-120H240q-33 0-56.5-23.5T160-200v-160Zm200-80q25 0 42.5-17.5T420-500q0-25-17.5-42.5T360-560q-25 0-42.5 17.5T300-500q0 25 17.5 42.5T360-440Zm240 0q25 0 42.5-17.5T660-500q0-25-17.5-42.5T600-560q-25 0-42.5 17.5T540-500q0 25 17.5 42.5T600-440ZM320-280h320v-80H320v80Zm-80 80h480v-480H240v480Zm240-240Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function botebook($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M40-120v-80h880v80H40Zm120-120q-33 0-56.5-23.5T80-320v-440q0-33 23.5-56.5T160-840h640q33 0 56.5 23.5T880-760v440q0 33-23.5 56.5T800-240H160Zm0-80h640v-440H160v440Zm0 0v-440 440Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function memoria($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M360-360v-240h240v240H360Zm80-80h80v-80h-80v80Zm-80 320v-80h-80q-33 0-56.5-23.5T200-280v-80h-80v-80h80v-80h-80v-80h80v-80q0-33 23.5-56.5T280-760h80v-80h80v80h80v-80h80v80h80q33 0 56.5 23.5T760-680v80h80v80h-80v80h80v80h-80v80q0 33-23.5 56.5T680-200h-80v80h-80v-80h-80v80h-80Zm320-160v-400H280v400h400ZM480-480Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function teclado($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M160-200q-33 0-56.5-23.5T80-280v-400q0-33 23.5-56.5T160-760h640q33 0 56.5 23.5T880-680v400q0 33-23.5 56.5T800-200H160Zm0-80h640v-400H160v400Zm160-40h320v-80H320v80ZM200-440h80v-80h-80v80Zm120 0h80v-80h-80v80Zm120 0h80v-80h-80v80Zm120 0h80v-80h-80v80Zm120 0h80v-80h-80v80ZM200-560h80v-80h-80v80Zm120 0h80v-80h-80v80Zm120 0h80v-80h-80v80Zm120 0h80v-80h-80v80Zm120 0h80v-80h-80v80ZM160-280v-400 400Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function mouse($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-80q-116 0-198-82t-82-198v-240q0-116 82-198t198-82q116 0 198 82t82 198v240q0 116-82 198T480-80Zm40-520h160q0-72-45.5-127T520-796v196Zm-240 0h160v-196q-69 14-114.5 69T280-600Zm200 440q83 0 141.5-58.5T680-360v-160H280v160q0 83 58.5 141.5T480-160Zm0-360Zm40-80Zm-80 0Zm40 80Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function balanca($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M80-120v-80h360v-447q-26-9-45-28t-28-45H240l120 280q0 50-41 85t-99 35q-58 0-99-35t-41-85l120-280h-80v-80h247q12-35 43-57.5t70-22.5q39 0 70 22.5t43 57.5h247v80h-80l120 280q0 50-41 85t-99 35q-58 0-99-35t-41-85l120-280H593q-9 26-28 45t-45 28v447h360v80H80Zm585-320h150l-75-174-75 174Zm-520 0h150l-75-174-75 174Zm335-280q17 0 28.5-11.5T520-760q0-17-11.5-28.5T480-800q-17 0-28.5 11.5T440-760q0 17 11.5 28.5T480-720Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function rota($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M360-120q-66 0-113-47t-47-113v-327q-35-13-57.5-43.5T120-720q0-50 35-85t85-35q50 0 85 35t35 85q0 39-22.5 69.5T280-607v327q0 33 23.5 56.5T360-200q33 0 56.5-23.5T440-280v-400q0-66 47-113t113-47q66 0 113 47t47 113v327q35 13 57.5 43.5T840-240q0 50-35 85t-85 35q-50 0-85-35t-35-85q0-39 22.5-70t57.5-43v-327q0-33-23.5-56.5T600-760q-33 0-56.5 23.5T520-680v400q0 66-47 113t-113 47ZM240-680q17 0 28.5-11.5T280-720q0-17-11.5-28.5T240-760q-17 0-28.5 11.5T200-720q0 17 11.5 28.5T240-680Zm480 480q17 0 28.5-11.5T760-240q0-17-11.5-28.5T720-280q-17 0-28.5 11.5T680-240q0 17 11.5 28.5T720-200ZM240-720Zm480 480Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function termometro($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-80q-83 0-141.5-58.5T280-280q0-48 21-89.5t59-70.5v-320q0-50 35-85t85-35q50 0 85 35t35 85v320q38 29 59 70.5t21 89.5q0 83-58.5 141.5T480-80Zm-40-440h80v-40h-40v-40h40v-80h-40v-40h40v-40q0-17-11.5-28.5T480-800q-17 0-28.5 11.5T440-760v240Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function tomada($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M460-200h40v-74l140-140v-186H320v186l140 140v74Zm-80 80v-120L240-380v-220q0-33 23.5-56.5T320-680h40l-40 40v-200h80v160h160v-160h80v200l-40-40h40q33 0 56.5 23.5T720-600v220L580-240v120H380Zm100-280Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function celularDev($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M344-296 160-480l184-184 56 58-126 126 126 126-56 58Zm-144 16h80v40h400v-40h80v160q0 33-23.5 56.5T680-40H280q-33 0-56.5-23.5T200-120v-160Zm80-400h-80v-160q0-33 23.5-56.5T280-920h400q33 0 56.5 23.5T760-840v160h-80v-40H280v40Zm0 520v40h400v-40H280Zm0-640h400v-40H280v40Zm336 504-56-58 126-126-126-126 56-58 184 184-184 184ZM280-800v-40 40Zm0 640v40-40Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function celularVerificado($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M240-40q-33 0-56.5-23.5T160-120v-720q0-33 23.5-56.5T240-920h400q33 0 56.5 23.5T720-840v160h-80v-40H240v480h400v-40h80v160q0 33-23.5 56.5T640-40H240Zm0-120v40h400v-40H240Zm358-160L428-490l56-56 114 114 226-226 56 56-282 282ZM240-800h400v-40H240v40Zm0 0v-40 40Zm0 640v40-40Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function celularVerificado2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M442-360 330-472l56-56 56 56 142-142 56 56-198 198ZM280-40q-33 0-56.5-23.5T200-120v-720q0-33 23.5-56.5T280-920h400q33 0 56.5 23.5T760-840v720q0 33-23.5 56.5T680-40H280Zm0-120v40h400v-40H280Zm0-80h400v-480H280v480Zm0-560h400v-40H280v40Zm0 0v-40 40Zm0 640v40-40Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function celularInterrogacao($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-280q-17 0-29.5-12.5T438-322q0-17 12.5-29.5T480-364q17 0 29.5 12.5T522-322q0 17-12.5 29.5T480-280Zm-30-128q0-46 7.5-63t42.5-47q14-14 24-27.5t10-30.5q0-18-13.5-32T480-622q-27 0-41 15.5T420-574l-54-22q12-35 41-59.5t73-24.5q47 0 80.5 25.5T594-578q0 24-12 45t-30 39q-30 30-36 42t-6 44h-60ZM280-40q-33 0-56.5-23.5T200-120v-720q0-33 23.5-56.5T280-920h400q33 0 56.5 23.5T760-840v720q0 33-23.5 56.5T680-40H280Zm0-120v40h400v-40H280Zm0-80h400v-480H280v480Zm0-560h400v-40H280v40Zm0 0v-40 40Zm0 640v40-40Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function celularCamera($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M280-40q-33 0-56.5-23.5T200-120v-720q0-33 23.5-56.5T280-920h400q33 0 56.5 23.5T760-840v120H280v480h480v120q0 33-23.5 56.5T680-40H280Zm0-80h400v-40H280v40Zm0-680h400v-40H280v40Zm0 0v-40 40Zm0 680v-40 40Zm300-200q-25 0-42.5-17.5T520-380v-160q0-25 17.5-42.5T580-600h40l40-40h80l40 40h40q25 0 42.5 17.5T880-540v160q0 25-17.5 42.5T820-320H580Zm120-70q29 0 49.5-20.5T770-460q0-29-20.5-49.5T700-530q-29 0-49.5 20.5T630-460q0 29 20.5 49.5T700-390Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function emojiIdioma($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M620-40q-104 0-183.5-62T331-260q45 2 89-9t84-31h164q1-11 1.5-21.5t.5-21.5q0-9-.5-18.5T668-380h-59q16-18 29.5-38t24.5-42h141q-20-30-48-52.5T693-547q5-20 6.5-41t.5-41q96 26 158 105.5T920-340q0 125-87.5 212.5T620-40Zm-95-102q-7-20-12.5-39t-9.5-39h-67q17 25 39.5 45t49.5 33Zm95 14q12-22 20.5-45t14.5-47h-70q6 24 15 47t20 45Zm95-14q27-13 49.5-33t39.5-45h-67q-5 20-10 39t-12 39Zm33-158h88q2-10 3-19.5t1-20.5q0-11-1-20.5t-3-19.5h-88q1 9 1.5 18.5t.5 18.5q0 11-.5 21.5T748-300Zm-408-20q-125 0-212.5-87.5T40-620q0-125 87.5-212.5T340-920q125 0 212.5 87.5T640-620q0 125-87.5 212.5T340-320Zm0-80q91 0 155.5-64.5T560-620q0-91-64.5-155.5T340-840q-91 0-155.5 64.5T120-620q0 91 64.5 155.5T340-400ZM240-640q17 0 28.5-11.5T280-680q0-17-11.5-28.5T240-720q-17 0-28.5 11.5T200-680q0 17 11.5 28.5T240-640Zm100 176q48 0 85.5-27t54.5-69H200q17 42 54.5 69t85.5 27Zm100-176q17 0 28.5-11.5T480-680q0-17-11.5-28.5T440-720q-17 0-28.5 11.5T400-680q0 17 11.5 28.5T440-640Zm-100 20Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function hd($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h480q33 0 56.5 23.5T800-800v640q0 33-23.5 56.5T720-80H240Zm0-80h480v-640H240v640Zm80-80h320v-80H320v80Zm160-160q66 0 113-47t47-113q0-66-47-113t-113-47q-66 0-113 47t-47 113q0 66 47 113t113 47Zm0-120q-17 0-28.5-11.5T440-560q0-17 11.5-28.5T480-600q17 0 28.5 11.5T520-560q0 17-11.5 28.5T480-520Zm0-40Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function wifi2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-120q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29ZM254-346l-84-86q59-59 138.5-93.5T480-560q92 0 171.5 35T790-430l-84 84q-44-44-102-69t-124-25q-66 0-124 25t-102 69ZM84-516 0-600q92-94 215-147t265-53q142 0 265 53t215 147l-84 84q-77-77-178.5-120.5T480-680q-116 0-217.5 43.5T84-516Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function wifi_off($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M790-56 414-434q-47 11-87.5 33T254-346l-84-86q32-32 69-56t79-42l-90-90q-41 21-76.5 46.5T84-516L0-602q32-32 66.5-57.5T140-708l-84-84 56-56 736 736-58 56Zm-310-64q-42 0-71-29.5T380-220q0-42 29-71t71-29q42 0 71 29t29 71q0 41-29 70.5T480-120Zm236-238-29-29-29-29-144-144q81 8 151.5 41T790-432l-74 74Zm160-158q-77-77-178.5-120.5T480-680q-21 0-40.5 1.5T400-674L298-776q44-12 89.5-18t92.5-6q142 0 265 53t215 145l-84 86Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function wifiBuscando($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-120 0-601q93-93 215.5-146T480-800q142 0 264.5 53T960-601l-56 57q-81-81-190-128.5T480-720q-103 0-195 32.5T117-597l419 420-56 57Zm384-40L761-262q-18 11-38 16.5t-43 5.5q-68 0-114-46t-46-114q0-68 46-114t114-46q68 0 114 46t46 114q0 23-5.5 43T818-319l102 103-56 56ZM680-320q34 0 57-23t23-57q0-34-23-57t-57-23q-34 0-57 23t-23 57q0 34 23 57t57 23ZM480-177Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function wifiSinalRuim($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-120q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29ZM254-346l-84-86q59-59 138.5-93.5T480-560q92 0 171.5 35T790-430l-84 84q-44-44-102-69t-124-25q-66 0-124 25t-102 69Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function bluetooth($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M440-80v-304L256-200l-56-56 224-224-224-224 56-56 184 184v-304h40l228 228-172 172 172 172L480-80h-40Zm80-496 76-76-76-74v150Zm0 342 76-74-76-76v150ZM200-420q-25 0-42.5-17.5T140-480q0-25 17.5-42.5T200-540q25 0 42.5 17.5T260-480q0 25-17.5 42.5T200-420Zm560 0q-25 0-42.5-17.5T700-480q0-25 17.5-42.5T760-540q25 0 42.5 17.5T820-480q0 25-17.5 42.5T760-420Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function bluetooth_off($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M792-56 624-224 480-80h-40v-304L256-200l-56-56 196-196L56-792l56-56 736 736-56 56ZM520-234l46-46-46-46v92Zm44-274-56-56 88-88-76-74v174l-80-80v-248h40l228 228-144 144Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function modoClaro($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-360q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35Zm0 80q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-680q83 0 141.5 58.5T680-480q0 83-58.5 141.5T480-280ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Zm326-268Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function modoEscuro($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-120q-150 0-255-105T120-480q0-150 105-255t255-105q14 0 27.5 1t26.5 3q-41 29-65.5 75.5T444-660q0 90 63 153t153 63q55 0 101-24.5t75-65.5q2 13 3 26.5t1 27.5q0 150-105 255T480-120Zm0-80q88 0 158-48.5T740-375q-20 5-40 8t-40 3q-123 0-209.5-86.5T364-660q0-20 3-40t8-40q-78 32-126.5 102T200-480q0 116 82 198t198 82Zm-10-270Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function brilho2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-28 346-160H160v-186L28-480l132-134v-186h186l134-132 134 132h186v186l132 134-132 134v186H614L480-28Zm0-252q83 0 141.5-58.5T680-480q0-83-58.5-141.5T480-680v400Zm0 140 100-100h140v-140l100-100-100-100v-140H580L480-820 380-720H240v140L140-480l100 100v140h140l100 100Zm0-340Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function sinalCelular($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M200-160v-240h120v240H200Zm240 0v-440h120v440H440Zm240 0v-640h120v640H680Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function sinalCelularBom($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m80-80 800-800v800H80Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function sinalCelularMediano($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m80-80 800-800v800H80Zm440-80h280v-526L520-406v246Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function sinalCelularRuim($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m80-80 800-800v800H80Zm193-80h527v-526L273-160Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function bateria($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M320-80q-17 0-28.5-11.5T280-120v-640q0-17 11.5-28.5T320-800h80v-80h160v80h80q17 0 28.5 11.5T680-760v640q0 17-11.5 28.5T640-80H320Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function bateriaAlerta($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M440-400h80v-240h-80v240Zm40 160q17 0 28.5-11.5T520-280q0-17-11.5-28.5T480-320q-17 0-28.5 11.5T440-280q0 17 11.5 28.5T480-240ZM320-80q-17 0-28.5-11.5T280-120v-640q0-17 11.5-28.5T320-800h80v-80h160v80h80q17 0 28.5 11.5T680-760v640q0 17-11.5 28.5T640-80H320Zm40-80h240v-560H360v560Zm0 0h240-240Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function bateriaBaixa($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M320-80q-17 0-28.5-11.5T280-120v-640q0-17 11.5-28.5T320-800h80v-80h160v80h80q17 0 28.5 11.5T680-760v640q0 17-11.5 28.5T640-80H320Zm40-240h240v-400H360v400Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function bateriaSem($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M320-80q-17 0-28.5-11.5T280-120v-640q0-17 11.5-28.5T320-800h80v-80h160v80h80q17 0 28.5 11.5T680-760v640q0 17-11.5 28.5T640-80H320Zm40-160h240v-480H360v480Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function bateriaBaixa2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M200-280q-17 0-28.5-11.5T160-320v-80H80v-160h80v-80q0-17 11.5-28.5T200-680h640q17 0 28.5 11.5T880-640v320q0 17-11.5 28.5T840-280H200Zm40-80h440v-240H240v240Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function lanterna($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M320-80v-440l-80-120v-240h480v240l-80 120v440H320Zm160-260q-25 0-42.5-17.5T420-400q0-25 17.5-42.5T480-460q25 0 42.5 17.5T540-400q0 25-17.5 42.5T480-340ZM320-760h320v-40H320v40Zm320 80H320v16l80 120v384h160v-384l80-120v-16ZM480-480Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function usb($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-80q-33 0-56.5-23.5T400-160q0-21 11-39t29-29v-92H320q-33 0-56.5-23.5T240-400v-92q-18-9-29-27t-11-41q0-33 23.5-56.5T280-640q33 0 56.5 23.5T360-560q0 23-11 40t-29 28v92h120v-320h-80l120-160 120 160h-80v320h120v-80h-40v-160h160v160h-40v80q0 33-23.5 56.5T640-320H520v92q19 10 29.5 28t10.5 40q0 33-23.5 56.5T480-80Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function sinal5g($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M120-280v-80h200v-80H120v-240h280v80H200v80h120q33 0 56.5 23.5T400-440v80q0 33-23.5 56.5T320-280H120Zm720-240v160q0 33-23.5 56.5T760-280H560q-33 0-56.5-23.5T480-360v-240q0-33 23.5-56.5T560-680h200q33 0 56.5 23.5T840-600H560v240h200v-80H660v-80h180Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function ios($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M160-600v-80h80v80h-80Zm0 320v-240h80v240h-80Zm280 0h-80q-33 0-56.5-23.5T280-360v-240q0-33 23.5-56.5T360-680h80q33 0 56.5 23.5T520-600v240q0 33-23.5 56.5T440-280Zm-80-80h80v-240h-80v240Zm200 80v-80h160v-80h-80q-33 0-56.5-23.5T560-520v-80q0-33 23.5-56.5T640-680h160v80H640v80h80q33 0 56.5 23.5T800-440v80q0 33-23.5 56.5T720-280H560Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function escudo($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-80q-139-35-229.5-159.5T160-516v-244l320-120 320 120v244q0 152-90.5 276.5T480-80Zm0-84q104-33 172-132t68-220v-189l-240-90-240 90v189q0 121 68 220t172 132Zm0-316Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function escudoVerificado($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m438-338 226-226-57-57-169 169-84-84-57 57 141 141Zm42 258q-139-35-229.5-159.5T160-516v-244l320-120 320 120v244q0 152-90.5 276.5T480-80Zm0-84q104-33 172-132t68-220v-189l-240-90-240 90v189q0 121 68 220t172 132Zm0-316Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function exclamacao($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M440-400v-360h80v360h-80Zm0 200v-80h80v80h-80Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function casa($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M200-160v-366L88-440l-48-64 440-336 160 122v-82h120v174l160 122-48 64-112-86v366H520v-240h-80v240H200Zm80-80h80v-240h240v240h80v-347L480-739 280-587v347Zm120-319h160q0-32-24-52.5T480-632q-32 0-56 20.5T400-559Zm-40 319v-240h240v240-240H360v240Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function vassoura($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M440-520h80v-280q0-17-11.5-28.5T480-840q-17 0-28.5 11.5T440-800v280ZM200-360h560v-80H200v80Zm-58 240h98v-80q0-17 11.5-28.5T280-240q17 0 28.5 11.5T320-200v80h120v-80q0-17 11.5-28.5T480-240q17 0 28.5 11.5T520-200v80h120v-80q0-17 11.5-28.5T680-240q17 0 28.5 11.5T720-200v80h98l-40-160H182l-40 160Zm676 80H142q-39 0-63-31t-14-69l55-220v-80q0-33 23.5-56.5T200-520h160v-280q0-50 35-85t85-35q50 0 85 35t35 85v280h160q33 0 56.5 23.5T840-440v80l55 220q13 38-11.5 69T818-40Zm-58-400H200h560Zm-240-80h-80 80Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function fogo($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M240-400q0 52 21 98.5t60 81.5q-1-5-1-9v-9q0-32 12-60t35-51l113-111 113 111q23 23 35 51t12 60v9q0 4-1 9 39-35 60-81.5t21-98.5q0-50-18.5-94.5T648-574q-20 13-42 19.5t-45 6.5q-62 0-107.5-41T401-690q-39 33-69 68.5t-50.5 72Q261-513 250.5-475T240-400Zm240 52-57 56q-11 11-17 25t-6 29q0 32 23.5 55t56.5 23q33 0 56.5-23t23.5-55q0-16-6-29.5T537-292l-57-56Zm0-492v132q0 34 23.5 57t57.5 23q18 0 33.5-7.5T622-658l18-22q74 42 117 117t43 163q0 134-93 227T480-80q-134 0-227-93t-93-227q0-129 86.5-245T480-840Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function gotaAgua($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-100q-133 0-226.5-92T160-416q0-63 24.5-120.5T254-638l226-222 226 222q45 44 69.5 101.5T800-416q0 132-93.5 224T480-100Zm0-80q100 0 170-68.5T720-416q0-47-18-89.5T650-580L480-748 310-580q-34 32-52 74.5T240-416q0 99 70 167.5T480-180Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function gotaAgua2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M491-200q12-1 20.5-9.5T520-230q0-14-9-22.5t-23-7.5q-41 3-87-22.5T343-375q-2-11-10.5-18t-19.5-7q-14 0-23 10.5t-6 24.5q17 91 80 130t127 35ZM480-80q-137 0-228.5-94T160-408q0-100 79.5-217.5T480-880q161 137 240.5 254.5T800-408q0 140-91.5 234T480-80Zm0-80q104 0 172-70.5T720-408q0-73-60.5-165T480-774Q361-665 300.5-573T240-408q0 107 68 177.5T480-160Zm0-320Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function lua($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M380-160q133 0 226.5-93.5T700-480q0-133-93.5-226.5T380-800h-21q-10 0-19 2 57 66 88.5 147.5T460-480q0 89-31.5 170.5T340-162q9 2 19 2h21Zm0 80q-53 0-103.5-13.5T180-134q93-54 146.5-146T380-480q0-108-53.5-200T180-826q46-27 96.5-40.5T380-880q83 0 156 31.5T663-763q54 54 85.5 127T780-480q0 83-31.5 156T663-197q-54 54-127 85.5T380-80Zm80-400Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function lampada_off($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-80q-33 0-56.5-23.5T400-160h160q0 33-23.5 56.5T480-80Zm0-720q-44 0-81.5 15.5T332-742l-58-56q41-38 93.5-60T480-880q125 0 212.5 87.5T780-580q0 71-25 121.5T698-376l-56-56q21-23 39.5-59t18.5-89q0-92-64-156t-156-64Zm368 688-57 57-265-265H330q-69-41-109.5-110T180-580q0-20 2.5-39t7.5-37L56-792l56-56 736 736ZM354-400h92L260-586v6q0 54 24.5 101t69.5 79Zm-6-98Zm134-94Zm164 312v80H320v-80h326Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function garagem($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M200-120v-406L88-440l-48-64 440-336 440 336-48 64-112-86v406h-80v-400H280v400h-80Zm120-40v-80h320v80H320Zm0-120v-80h320v80H320Zm0-120v-80h320v80H320Zm-23-200h366L480-739 297-600Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function controle3($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M189-160q-60 0-102.5-43T42-307q0-9 1-18t3-18l84-336q14-54 57-87.5t98-33.5h390q55 0 98 33.5t57 87.5l84 336q2 9 3.5 18.5T919-306q0 61-43.5 103.5T771-160q-42 0-78-22t-54-60l-28-58q-5-10-15-15t-21-5H385q-11 0-21 5t-15 15l-28 58q-18 38-54 60t-78 22Zm3-80q19 0 34.5-10t23.5-27l28-57q15-31 44-48.5t63-17.5h190q34 0 63 18t45 48l28 57q8 17 23.5 27t34.5 10q28 0 48-18.5t21-46.5q0 1-2-19l-84-335q-7-27-28-44t-49-17H285q-28 0-49.5 17T208-659l-84 335q-2 6-2 18 0 28 20.5 47t49.5 19Zm348-280q17 0 28.5-11.5T580-560q0-17-11.5-28.5T540-600q-17 0-28.5 11.5T500-560q0 17 11.5 28.5T540-520Zm80-80q17 0 28.5-11.5T660-640q0-17-11.5-28.5T620-680q-17 0-28.5 11.5T580-640q0 17 11.5 28.5T620-600Zm0 160q17 0 28.5-11.5T660-480q0-17-11.5-28.5T620-520q-17 0-28.5 11.5T580-480q0 17 11.5 28.5T620-440Zm80-80q17 0 28.5-11.5T740-560q0-17-11.5-28.5T700-600q-17 0-28.5 11.5T660-560q0 17 11.5 28.5T700-520Zm-360 60q13 0 21.5-8.5T370-490v-40h40q13 0 21.5-8.5T440-560q0-13-8.5-21.5T410-590h-40v-40q0-13-8.5-21.5T340-660q-13 0-21.5 8.5T310-630v40h-40q-13 0-21.5 8.5T240-560q0 13 8.5 21.5T270-530h40v40q0 13 8.5 21.5T340-460Zm140-20Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function relogio3($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M582-298 440-440v-200h80v167l118 118-56 57ZM440-720v-80h80v80h-80Zm280 280v-80h80v80h-80ZM440-160v-80h80v80h-80ZM160-440v-80h80v80h-80ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function chuva($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M260-200q-21 0-35.5-14.5T210-250q0-21 14.5-35.5T260-300q21 0 35.5 14.5T310-250q0 21-14.5 35.5T260-200ZM380-80q-21 0-35.5-14.5T330-130q0-21 14.5-35.5T380-180q21 0 35.5 14.5T430-130q0 21-14.5 35.5T380-80Zm120-120q-21 0-35.5-14.5T450-250q0-21 14.5-35.5T500-300q21 0 35.5 14.5T550-250q0 21-14.5 35.5T500-200Zm240 0q-21 0-35.5-14.5T690-250q0-21 14.5-35.5T740-300q21 0 35.5 14.5T790-250q0 21-14.5 35.5T740-200ZM620-80q-21 0-35.5-14.5T570-130q0-21 14.5-35.5T620-180q21 0 35.5 14.5T670-130q0 21-14.5 35.5T620-80ZM300-360q-91 0-155.5-64.5T80-580q0-83 55-145t136-73q32-57 87.5-89.5T480-920q90 0 156.5 57.5T717-719q69 6 116 57t47 122q0 75-52.5 127.5T700-360H300Zm0-80h400q42 0 71-29t29-71q0-42-29-71t-71-29h-60v-40q0-66-47-113t-113-47q-48 0-87.5 26T333-744l-10 24h-25q-57 2-97.5 42.5T160-580q0 58 41 99t99 41Zm180-100Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function controleRemoto($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-680q-25 0-42.5-17.5T420-740q0-25 17.5-42.5T480-800q25 0 42.5 17.5T540-740q0 25-17.5 42.5T480-680Zm0 640q-83 0-141.5-58.5T280-240v-480q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v480q0 83-58.5 141.5T480-40Zm0-600q42 0 71-29t29-71q0-42-29-71t-71-29q-42 0-71 29t-29 71q0 42 29 71t71 29Zm0 520q50 0 85-35t35-85v-367q-25 23-56 35t-64 12q-33 0-64-12t-56-35v367q0 50 35 85t85 35Zm-60-200q17 0 28.5-11.5T460-360q0-17-11.5-28.5T420-400q-17 0-28.5 11.5T380-360q0 17 11.5 28.5T420-320Zm0-120q17 0 28.5-11.5T460-480q0-17-11.5-28.5T420-520q-17 0-28.5 11.5T380-480q0 17 11.5 28.5T420-440Zm120 0q17 0 28.5-11.5T580-480q0-17-11.5-28.5T540-520q-17 0-28.5 11.5T500-480q0 17 11.5 28.5T540-440Zm0 120q17 0 28.5-11.5T580-360q0-17-11.5-28.5T540-400q-17 0-28.5 11.5T500-360q0 17 11.5 28.5T540-320ZM420-200q17 0 28.5-11.5T460-240q0-17-11.5-28.5T420-280q-17 0-28.5 11.5T380-240q0 17 11.5 28.5T420-200Zm120 0q17 0 28.5-11.5T580-240q0-17-11.5-28.5T540-280q-17 0-28.5 11.5T500-240q0 17 11.5 28.5T540-200Zm-60-360Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function alicate($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M280-40q-20-40-30-98t-10-118q0-99 22-163.5T320-520v-200l120-200h80l120 200v200q37 36 58.5 100.5T720-256q0 60-10 118t-30 98q-42-13-67-47.5T589-166q0-25 5.5-54t5.5-58q0-58-31.5-122T480-520q-56 56-88 120t-32 122q0 29 6 58t7 54q0 44-25.5 79T280-40Zm200-600q-17 0-28.5-11.5T440-680q0-11 5-20t15-14v-85l-60 101v98h160v-98l-60-101v85q10 5 15 14t5 20q0 17-11.5 28.5T480-640Zm20-159h-40 40Zm-40 0h40-40Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function cameraSeguranca($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M563-150q-11 13-27.5 14.5T506-145L353-273q-50-42-72-102t-11-123l-56-47q-12-11-13.5-26.5T209-600h-49v80q0 33-23.5 56.5T80-440v-400q33 0 56.5 23.5T160-760v80h115l62-75q11-13 27.5-14.5T394-760l55 46q61-23 124-12t113 53l153 128q13 11 14.5 27.5T844-489L563-150Zm-36-83 231-277-124-102q-42-34-95.5-33T434-623l-61-50-77 92 60 50q-12 54-3 107.5t51 88.5l123 102Zm0-220Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function cameraSeguranca2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M360-80q-33 0-56.5-23.5T280-160h160v-163q-104-15-172-93.5T200-600q0-117 81.5-198.5T480-880q117 0 198.5 81.5T760-600q0 105-68 183.5T520-323v163h160q0 33-23.5 56.5T600-80H360Zm120-320q83 0 141.5-58.5T680-600q0-83-58.5-141.5T480-800q-83 0-141.5 58.5T280-600q0 83 58.5 141.5T480-400Zm0-120q-33 0-56.5-23.5T400-600q0-33 23.5-56.5T480-680q33 0 56.5 23.5T560-600q0 33-23.5 56.5T480-520Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function chave($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M280-400q-33 0-56.5-23.5T200-480q0-33 23.5-56.5T280-560q33 0 56.5 23.5T360-480q0 33-23.5 56.5T280-400Zm0 160q-100 0-170-70T40-480q0-100 70-170t170-70q67 0 121.5 33t86.5 87h352l120 120-180 180-80-60-80 60-85-60h-47q-32 54-86.5 87T280-240Zm0-80q56 0 98.5-34t56.5-86h125l58 41 82-61 71 55 75-75-40-40H435q-14-52-56.5-86T280-640q-66 0-113 47t-47 113q0 66 47 113t113 47Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function sincronizar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M160-160v-80h110l-16-14q-52-46-73-105t-21-119q0-111 66.5-197.5T400-790v84q-72 26-116 88.5T240-478q0 45 17 87.5t53 78.5l10 10v-98h80v240H160Zm400-10v-84q72-26 116-88.5T720-482q0-45-17-87.5T650-648l-10-10v98h-80v-240h240v80H690l16 14q49 49 71.5 106.5T800-482q0 111-66.5 197.5T560-170Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function desfazer($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M280-200v-80h284q63 0 109.5-40T720-420q0-60-46.5-100T564-560H312l104 104-56 56-200-200 200-200 56 56-104 104h252q97 0 166.5 63T800-420q0 94-69.5 157T564-200H280Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function apagar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m456-320 104-104 104 104 56-56-104-104 104-104-56-56-104 104-104-104-56 56 104 104-104 104 56 56Zm-96 160q-19 0-36-8.5T296-192L80-480l216-288q11-15 28-23.5t36-8.5h440q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H360ZM180-480l180 240h440v-480H360L180-480Zm400 0Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function estrelaMetade($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m606-286-33-144 111-96-146-13-58-136v312l126 77ZM233-120l65-281L80-590l288-25 112-265 112 265 288 25-218 189 65 281-247-149-247 149Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function checkBox2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q8 0 15 1.5t14 4.5l-74 74H200v560h560v-266l80-80v346q0 33-23.5 56.5T760-120H200Zm261-160L235-506l56-56 170 170 367-367 57 55-424 424Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function qrcode($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M520-120v-80h80v80h-80Zm-80-80v-200h80v200h-80Zm320-120v-160h80v160h-80Zm-80-160v-80h80v80h-80Zm-480 80v-80h80v80h-80Zm-80-80v-80h80v80h-80Zm360-280v-80h80v80h-80ZM180-660h120v-120H180v120Zm-60 60v-240h240v240H120Zm60 420h120v-120H180v120Zm-60 60v-240h240v240H120Zm540-540h120v-120H660v120Zm-60 60v-240h240v240H600Zm80 480v-120h-80v-80h160v120h80v80H680ZM520-400v-80h160v80H520Zm-160 0v-80h-80v-80h240v80h-80v80h-80Zm40-200v-160h80v80h80v80H400Zm-190-90v-60h60v60h-60Zm0 480v-60h60v60h-60Zm480-480v-60h60v60h-60Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function chave2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M280-240q-100 0-170-70T40-480q0-100 70-170t170-70q66 0 121 33t87 87h432v240h-80v120H600v-120H488q-32 54-87 87t-121 33Zm0-80q66 0 106-40.5t48-79.5h246v120h80v-120h80v-80H434q-8-39-48-79.5T280-640q-66 0-113 47t-47 113q0 66 47 113t113 47Zm0-80q33 0 56.5-23.5T360-480q0-33-23.5-56.5T280-560q-33 0-56.5 23.5T200-480q0 33 23.5 56.5T280-400Zm0-80Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function chave_off($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M791-55 486-360q-32 54-85.5 87T280-240q-100 0-170-70T40-480q0-66 32-121t86-87L55-791l57-57 736 736-57 57ZM680-393l-47-47h47v47Zm160 160-80-80v-127h80v-80H553l-80-80h447v240h-80v127Zm-560-87q65 0 99.5-35t49.5-63L219-628q-44 18-71.5 57.5T120-480q0 66 47 113t113 47Zm0-80q-33 0-56.5-23.5T200-480q0-33 23.5-56.5T280-560q33 0 56.5 23.5T360-480q0 33-23.5 56.5T280-400Zm309-84Zm-314 10Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function userChave($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M120-160v-112q0-34 17.5-62.5T184-378q62-31 126-46.5T440-440q20 0 40 1.5t40 4.5q-4 58 21 109.5t73 84.5v80H120ZM760-40l-60-60v-186q-44-13-72-49.5T600-420q0-58 41-99t99-41q58 0 99 41t41 99q0 45-25.5 80T790-290l50 50-60 60 60 60-80 80ZM440-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm300 80q17 0 28.5-11.5T780-440q0-17-11.5-28.5T740-480q-17 0-28.5 11.5T700-440q0 17 11.5 28.5T740-400Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function lupa($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function lupaZoomMais($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Zm-40-60v-80h-80v-80h80v-80h80v80h80v80h-80v80h-80Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function lupaZoomMenos($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400ZM280-540v-80h200v80H280Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function buscarNaPagina($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m590-160 80 80H240q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h360l200 240v480q0 20-8.5 36.5T768-96L560-302q-17 11-37 16.5t-43 5.5q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 43T618-360l102 104v-356L562-800H240v640h350ZM480-360q33 0 56.5-23.5T560-440q0-33-23.5-56.5T480-520q-33 0-56.5 23.5T400-440q0 33 23.5 56.5T480-360Zm0-80Zm0 0Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function buscarImagem($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M240-280h480L597-444q-11-2-22.5-5t-22.5-7L450-320l-90-120-120 160Zm-40 160q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h200v80H200v560h560v-213l80 80v133q0 33-23.5 56.5T760-120H200Zm280-360Zm382 56L738-548q-21 14-45 21t-51 7q-74 0-126-52.5T464-700q0-75 52.5-127.5T644-880q75 0 127.5 52.5T824-700q0 27-8 52t-20 46l122 122-56 56ZM644-600q42 0 71-29t29-71q0-42-29-71t-71-29q-42 0-71 29t-29 71q0 42 29 71t71 29Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function buscarUsuario($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M440-560q33 0 56.5-23.5T520-640q0-33-23.5-56.5T440-720q-33 0-56.5 23.5T360-640q0 33 23.5 56.5T440-560Zm0 160q45 0 84.5-19t68.5-54q-35-23-73.5-35T440-520q-41 0-79.5 12T287-473q29 35 68.5 54t84.5 19Zm384 280L636-308q-41 32-90.5 50T440-240q-134 0-227-93t-93-227q0-134 93-227t227-93q134 0 227 93t93 227q0 56-18 105.5T692-364l188 188-56 56ZM440-320q100 0 170-70t70-170q0-100-70-170t-170-70q-100 0-170 70t-70 170q0 100 70 170t170 70Zm0-240Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function compartilhar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M680-80q-50 0-85-35t-35-85q0-6 3-28L282-392q-16 15-37 23.5t-45 8.5q-50 0-85-35t-35-85q0-50 35-85t85-35q24 0 45 8.5t37 23.5l281-164q-2-7-2.5-13.5T560-760q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35q-24 0-45-8.5T598-672L317-508q2 7 2.5 13.5t.5 14.5q0 8-.5 14.5T317-452l281 164q16-15 37-23.5t45-8.5q50 0 85 35t35 85q0 50-35 85t-85 35Zm0-80q17 0 28.5-11.5T720-200q0-17-11.5-28.5T680-240q-17 0-28.5 11.5T640-200q0 17 11.5 28.5T680-160ZM200-440q17 0 28.5-11.5T240-480q0-17-11.5-28.5T200-520q-17 0-28.5 11.5T160-480q0 17 11.5 28.5T200-440Zm480-280q17 0 28.5-11.5T720-760q0-17-11.5-28.5T680-800q-17 0-28.5 11.5T640-760q0 17 11.5 28.5T680-720Zm0 520ZM200-480Zm480-280Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function polegar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M720-120H280v-520l280-280 50 50q7 7 11.5 19t4.5 23v14l-44 174h258q32 0 56 24t24 56v80q0 7-2 15t-4 15L794-168q-9 20-30 34t-44 14Zm-360-80h360l120-280v-80H480l54-220-174 174v406Zm0-406v406-406Zm-80-34v80H160v360h120v80H80v-520h200Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function polegarBaixo($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M240-840h440v520L400-40l-50-50q-7-7-11.5-19t-4.5-23v-14l44-174H120q-32 0-56-24t-24-56v-80q0-7 2-15t4-15l120-282q9-20 30-34t44-14Zm360 80H240L120-480v80h360l-54 220 174-174v-406Zm0 406v-406 406Zm80 34v-80h120v-360H680v-80h200v520H680Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function foguete2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m226-559 78 33q14-28 29-54t33-52l-56-11-84 84Zm142 83 114 113q42-16 90-49t90-75q70-70 109.5-155.5T806-800q-72-5-158 34.5T492-656q-42 42-75 90t-49 90Zm178-65q-23-23-23-56.5t23-56.5q23-23 57-23t57 23q23 23 23 56.5T660-541q-23 23-57 23t-57-23Zm19 321 84-84-11-56q-26 18-52 32.5T532-299l33 79Zm313-653q19 121-23.5 235.5T708-419l20 99q4 20-2 39t-20 33L538-80l-84-197-171-171-197-84 167-168q14-14 33.5-20t39.5-2l99 20q104-104 218-147t235-24ZM157-321q35-35 85.5-35.5T328-322q35 35 34.5 85.5T327-151q-25 25-83.5 43T82-76q14-103 32-161.5t43-83.5Zm57 56q-10 10-20 36.5T180-175q27-4 53.5-13.5T270-208q12-12 13-29t-11-29q-12-12-29-11.5T214-265Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function buscarMundo($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q146 0 255.5 91.5T872-559h-82q-19-73-68.5-130.5T600-776v16q0 33-23.5 56.5T520-680h-80v80q0 17-11.5 28.5T400-560h-80v80h80v120h-40L168-552q-3 18-5.5 36t-2.5 36q0 131 92 225t228 95v80Zm364-20L716-228q-21 12-45 20t-51 8q-75 0-127.5-52.5T440-380q0-75 52.5-127.5T620-560q75 0 127.5 52.5T800-380q0 27-8 51t-20 45l128 128-56 56ZM620-280q42 0 71-29t29-71q0-42-29-71t-71-29q-42 0-71 29t-29 71q0 42 29 71t71 29Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function medalha($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m387-412 35-114-92-74h114l36-112 36 112h114l-93 74 35 114-92-71-93 71ZM240-40v-309q-38-42-59-96t-21-115q0-134 93-227t227-93q134 0 227 93t93 227q0 61-21 115t-59 96v309l-240-80-240 80Zm240-280q100 0 170-70t70-170q0-100-70-170t-170-70q-100 0-170 70t-70 170q0 100 70 170t170 70ZM320-159l160-41 160 41v-124q-35 20-75.5 31.5T480-240q-44 0-84.5-11.5T320-283v124Zm160-62Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function pata($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M180-475q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29Zm180-160q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29Zm240 0q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29Zm180 160q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29ZM266-75q-45 0-75.5-34.5T160-191q0-52 35.5-91t70.5-77q29-31 50-67.5t50-68.5q22-26 51-43t63-17q34 0 63 16t51 42q28 32 49.5 69t50.5 69q35 38 70.5 77t35.5 91q0 47-30.5 81.5T694-75q-54 0-107-9t-107-9q-54 0-107 9t-107 9Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function virus($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M450-80q-12 0-21-9t-9-21q0-12 9-21t21-9v-62q-42-5-79-20t-67-40l-43 44q9 9 9 21t-9 21q-9 9-21 9t-21-9l-43-42q-9-9-9-21.5t9-21.5q9-9 21-8.5t21 8.5l44-44q-25-31-40-67t-20-78h-62q0 12-9 21t-21 9q-12 0-21-9t-9-21v-60q0-12 9-21t21-9q12 0 21 9t9 21h62q5-42 20.5-78t39.5-67l-44-44q-9 8-21 8.5t-21-8.5q-9-9-9-21.5t9-21.5l42-42q9-9 21.5-9t21.5 9q9 9 9 21.5t-9 21.5l43 43q31-25 67-40t78-20v-62q-12 0-20.5-9t-8.5-21q0-12 9-21t21-9h60q12 0 21 9t9 21q0 12-9 21t-21 9v62q42 5 78 20t67 40l44-44q-9-9-9-21t9-21q9-9 21.5-9t21.5 9l42 43q9 9 9 21t-9 21q-9 9-21.5 9t-21.5-9l-43 43q25 31 40 67.5t20 78.5h62q0-12 9-21t21-9q12 0 21 9t9 21v60q0 12-9 21t-21 9q-12 0-21-9t-9-21h-62q-5 42-20 78.5T698-304l43 43q9-9 21.5-9t21.5 9q9 9 9 21.5t-9 21.5l-42 42q-9 9-21.5 9t-21.5-9q-9-9-8.5-21t8.5-21l-44-44q-31 25-67 40.5T510-201v61q12 0 21 9t9 21q0 12-9 21t-21 9h-60Zm30-200q83 0 141.5-58.5T680-480q0-83-58.5-141.5T480-680q-83 0-141.5 58.5T280-480q0 83 58.5 141.5T480-280Zm-70-40q17 0 28.5-11.5T450-360q0-17-11.5-28.5T410-400q-17 0-28.5 11.5T370-360q0 17 11.5 28.5T410-320Zm140 0q17 0 28.5-11.5T590-360q0-17-11.5-28.5T550-400q-17 0-28.5 11.5T510-360q0 17 11.5 28.5T550-320ZM340-440q17 0 28.5-11.5T380-480q0-17-11.5-28.5T340-520q-17 0-28.5 11.5T300-480q0 17 11.5 28.5T340-440Zm140 0q17 0 28.5-11.5T520-480q0-17-11.5-28.5T480-520q-17 0-28.5 11.5T440-480q0 17 11.5 28.5T480-440Zm140 0q17 0 28.5-11.5T660-480q0-17-11.5-28.5T620-520q-17 0-28.5 11.5T580-480q0 17 11.5 28.5T620-440ZM410-560q17 0 28.5-11.5T450-600q0-17-11.5-28.5T410-640q-17 0-28.5 11.5T370-600q0 17 11.5 28.5T410-560Zm140 0q17 0 28.5-11.5T590-600q0-17-11.5-28.5T550-640q-17 0-28.5 11.5T510-600q0 17 11.5 28.5T550-560Zm-70 80Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function trovao($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m300-40 36-100h-76l50-140h100l-43 100h83L340-40h-40Zm270-40 28-80h-78l43-120h100l-35 80h82L610-80h-40ZM300-320q-91 0-155.5-64.5T80-540q0-83 55-145t136-73q32-57 87.5-89.5T480-880q90 0 156.5 57.5T717-679q69 6 116 57t47 122q0 75-52.5 127.5T700-320H300Zm0-80h400q42 0 71-29t29-71q0-42-29-71t-71-29h-60v-40q0-66-47-113t-113-47q-48 0-87.5 26T333-704l-10 24h-25q-57 2-97.5 42.5T160-540q0 58 41 99t99 41Zm180-200Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function pilula($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M345-120q-94 0-159.5-65.5T120-345q0-45 17-86t49-73l270-270q32-32 73-49t86-17q94 0 159.5 65.5T840-615q0 45-17 86t-49 73L504-186q-32 32-73 49t-86 17Zm266-286 107-106q20-20 31-47t11-56q0-60-42.5-102.5T615-760q-29 0-56 11t-47 31L406-611l205 205ZM345-200q29 0 56-11t47-31l106-107-205-205-107 106q-20 20-31 47t-11 56q0 60 42.5 102.5T345-200Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function dna($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M200-40v-40q0-139 58-225.5T418-480q-102-88-160-174.5T200-880v-40h80v40q0 11 .5 20.5T282-840h396q1-10 1.5-19.5t.5-20.5v-40h80v40q0 139-58 225.5T542-480q102 88 160 174.5T760-80v40h-80v-40q0-11-.5-20.5T678-120H282q-1 10-1.5 19.5T280-80v40h-80Zm138-640h284q13-19 22.5-38t17.5-42H298q8 22 17.5 41.5T338-680Zm142 148q20-17 39-34t36-34H405q17 17 36 34t39 34Zm-75 172h150q-17-17-36-34t-39-34q-20 17-39 34t-36 34ZM298-200h364q-8-22-17.5-41.5T622-280H338q-13 19-22.5 38T298-200Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function mascara($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M312-240q-51 0-97.5-18T131-311q-48-45-69.5-106.5T40-545q0-78 38-126.5T189-720q14 0 26.5 2.5T241-710l239 89 239-89q13-5 25.5-7.5T771-720q73 0 111 48.5T920-545q0 66-21.5 127.5T829-311q-37 35-83.5 53T648-240q-66 0-112-30l-46-30h-20l-46 30q-46 30-112 30Zm0-80q37 0 69-17.5t59-42.5h80q27 25 59 42.5t69 17.5q36 0 69.5-12.5T777-371q34-34 48.5-80t14.5-94q0-41-17-68.5T769-640q-3 0-22 4L480-536 213-636q-5-2-10.5-3t-11.5-1q-37 0-54 27t-17 68q0 49 14.5 95t49.5 80q26 25 59 37.5t69 12.5Zm49-60q37 0 58-16.5t21-45.5q0-49-64.5-93.5T239-580q-37 0-58 16.5T160-518q0 49 64.5 93.5T361-380Zm-6-60q-38 0-82.5-25T220-516q5-2 11.5-3.5T245-521q38 0 82.5 25.5T380-444q-5 2-11.5 3t-13.5 1Zm244 61q72 0 136.5-45t64.5-94q0-29-20.5-46T721-581q-72 0-136.5 45T520-442q0 29 21 46t58 17Zm6-61q-7 0-13-1t-11-3q8-26 52.5-51t82.5-25q7 0 13 1t11 3q-8 26-52.5 51T605-440Zm-125-40Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function flor($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M426-160q-9-26-23-48t-33-41q-19-19-41-33.5T281-306q2 29 14 54t32 45q20 20 45 32.5t54 14.5Zm108 0q29-3 54-15t45-32q20-20 32-45t15-54q-26 9-48.5 23T590-250q-19 19-33 41.5T534-160Zm-54-360q66 0 113-47t47-113v-48l-70 59-90-109-90 109-70-59v48q0 66 47 113t113 47ZM440-80q-100 0-170-70t-70-170v-80q71-1 134 29t106 81v-153q-86-14-143-80.5T240-680v-136q0-26 23-36.5t43 6.5l74 64 69-84q12-14 31-14t31 14l69 84 74-64q20-17 43-6.5t23 36.5v136q0 90-57 156.5T520-443v153q43-51 106-81t134-29v80q0 100-70 170T520-80h-80Zm40-569Zm127 416Zm-253 0Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function bomba($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M346-48q-125 0-212.5-88.5T46-350q0-125 86.5-211.5T344-648h13l27-47q12-22 36-28.5t46 6.5l30 17 5-8q23-43 72-56t92 12l35 20-40 69-35-20q-14-8-30.5-3.5T570-668l-5 8 40 23q21 12 27.5 36t-5.5 45l-27 48q23 36 34.5 76.5T646-348q0 125-87.5 212.5T346-48Zm0-80q91 0 155.5-64.5T566-348q0-31-8.5-61T532-466l-26-41 42-72-104-60-42 72h-44q-94 0-163.5 60T125-350q0 92 64.5 157T346-128Zm454-480v-80h120v80H800ZM580-828v-120h80v120h-80Zm195 81-56-56 85-85 56 56-85 85ZM346-348Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function digital($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M481-781q106 0 200 45.5T838-604q7 9 4.5 16t-8.5 12q-6 5-14 4.5t-14-8.5q-55-78-141.5-119.5T481-741q-97 0-182 41.5T158-580q-6 9-14 10t-14-4q-7-5-8.5-12.5T126-602q62-85 155.5-132T481-781Zm0 94q135 0 232 90t97 223q0 50-35.5 83.5T688-257q-51 0-87.5-33.5T564-374q0-33-24.5-55.5T481-452q-34 0-58.5 22.5T398-374q0 97 57.5 162T604-121q9 3 12 10t1 15q-2 7-8 12t-15 3q-104-26-170-103.5T358-374q0-50 36-84t87-34q51 0 87 34t36 84q0 33 25 55.5t59 22.5q34 0 58-22.5t24-55.5q0-116-85-195t-203-79q-118 0-203 79t-85 194q0 24 4.5 60t21.5 84q3 9-.5 16T208-205q-8 3-15.5-.5T182-217q-15-39-21.5-77.5T154-374q0-133 96.5-223T481-687Zm0-192q64 0 125 15.5T724-819q9 5 10.5 12t-1.5 14q-3 7-10 11t-17-1q-53-27-109.5-41.5T481-839q-58 0-114 13.5T260-783q-8 5-16 2.5T232-791q-4-8-2-14.5t10-11.5q56-30 117-46t124-16Zm0 289q93 0 160 62.5T708-374q0 9-5.5 14.5T688-354q-8 0-14-5.5t-6-14.5q0-75-55.5-125.5T481-550q-76 0-130.5 50.5T296-374q0 81 28 137.5T406-123q6 6 6 14t-6 14q-6 6-14 6t-14-6q-59-62-90.5-126.5T256-374q0-91 66-153.5T481-590Zm-1 196q9 0 14.5 6t5.5 14q0 75 54 123t126 48q6 0 17-1t23-3q9-2 15.5 2.5T744-191q2 8-3 14t-13 8q-18 5-31.5 5.5t-16.5.5q-89 0-154.5-60T460-374q0-8 5.5-14t14.5-6Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function digital_off($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M833-41 435-440q-19 11-29 28.5T396-374q0 92 55.5 161T601-120q8 2 12 9.5t2 15.5q-2 8-9 12t-15 2q-108-28-171.5-107.5T356-374q0-28 13-52.5t37-41.5l-42-42q-34 26-52 61t-18 75q0 75 25.5 132T405-123q6 6 6 14.5T405-94q-6 6-14.5 6T376-94q-66-67-94-130.5T254-374q0-48 22-91.5t60-73.5l-44-43q-58 54-80 99t-22 109q0 36 6.5 73t20.5 72q3 8 0 15t-11 10q-8 3-15.5 0T180-215q-15-42-22-81t-7-78q0-73 25.5-127.5T264-611l-41-41q-20 17-36.5 34.5T156-580q-4 7-12 8.5t-16-3.5q-7-5-8-13t4-15q14-21 32-40t39-37L42-834l42-42L876-84l-43 43ZM688-354q-8 0-14-5.5t-6-14.5q0-72-51-121t-121-54l-40-40q6-1 12.5-1H481q93 0 160 62.5T708-374q0 9-5.5 14.5T688-354ZM481-879q64 0 125 15.5T724-819q9 5 10.5 12t-1.5 14q-3 7-10 11t-17-1q-53-27-109.5-41.5T481-839q-58 0-113.5 13T261-784l-29-29q57-32 120-49t129-17Zm0 98q106 0 200 45.5T838-604q7 9 4.5 16t-8.5 12q-6 5-14 4.5t-14-8.5q-55-78-141.5-119.5T481-741q-39 0-76.5 7T332-713l-30-30q42-19 86.5-28.5T481-781Zm0 94q135 0 232 90t97 223q0 29-13 52.5T763-282l-28-28q16-11 25.5-27.5T770-374q0-116-85-195t-203-79q-20 0-38.5 2.5T407-638l-32-32q25-8 51.5-12.5T481-687Zm193 525q-89 0-152.5-61T458-373q0-8 5.5-14t14.5-6q9 0 14.5 6t5.5 14q0 72 52 121t124 49q6 0 17-.5t23-2.5q9-2 15.5 2.5T738-190q2 8-3 14t-13 8q-18 5-31.5 5.5t-16.5.5Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function ligar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-84 31.5-156.5T197-763l56 56q-44 44-68.5 102T160-480q0 134 93 227t227 93q134 0 227-93t93-227q0-67-24.5-125T707-707l56-56q54 54 85.5 126.5T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm-40-360v-440h80v440h-80Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function interrogacao($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M424-320q0-81 14.5-116.5T500-514q41-36 62.5-62.5T584-637q0-41-27.5-68T480-732q-51 0-77.5 31T365-638l-103-44q21-64 77-111t141-47q105 0 161.5 58.5T698-641q0 50-21.5 85.5T609-475q-49 47-59.5 71.5T539-320H424Zm56 240q-33 0-56.5-23.5T400-160q0-33 23.5-56.5T480-240q33 0 56.5 23.5T560-160q0 33-23.5 56.5T480-80Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function traduzir($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m476-80 182-480h84L924-80h-84l-43-122H603L560-80h-84ZM160-200l-56-56 202-202q-35-35-63.5-80T190-640h84q20 39 40 68t48 58q33-33 68.5-92.5T484-720H40v-80h280v-80h80v80h280v80H564q-21 72-63 148t-83 116l96 98-30 82-122-125-202 201Zm468-72h144l-72-204-72 204Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function etiqueta($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h440q19 0 36 8.5t28 23.5l216 288-216 288q-11 15-28 23.5t-36 8.5H160Zm0-80h440l180-240-180-240H160v480Zm220-240Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function etiqueta2($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M446-80q-15 0-30-6t-27-18L103-390q-12-12-17.5-26.5T80-446q0-15 5.5-30t17.5-27l352-353q11-11 26-17.5t31-6.5h287q33 0 56.5 23.5T879-800v287q0 16-6 30.5T856-457L503-104q-12 12-27 18t-30 6Zm0-80 353-354v-286H513L160-446l286 286Zm253-480q25 0 42.5-17.5T759-700q0-25-17.5-42.5T699-760q-25 0-42.5 17.5T639-700q0 25 17.5 42.5T699-640ZM480-480Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function ampulheta($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M320-160h320v-120q0-66-47-113t-113-47q-66 0-113 47t-47 113v120Zm160-360q66 0 113-47t47-113v-120H320v120q0 66 47 113t113 47ZM160-80v-80h80v-120q0-61 28.5-114.5T348-480q-51-32-79.5-85.5T240-680v-120h-80v-80h640v80h-80v120q0 61-28.5 114.5T612-480q51 32 79.5 85.5T720-280v120h80v80H160Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function favorito($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M160-80v-560q0-33 23.5-56.5T240-720h320q33 0 56.5 23.5T640-640v560L400-200 160-80Zm80-121 160-86 160 86v-439H240v439Zm480-39v-560H280v-80h440q33 0 56.5 23.5T800-800v560h-80ZM240-640h320-320Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function triangulo($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m80-160 400-640 400 640H80Zm144-80h512L480-650 224-240Zm256-205Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function estrelas($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m668-380 152-130 120 10-176 153 52 227-102-62-46-198Zm-94-292-42-98 46-110 92 217-96-9ZM294-287l126-76 126 77-33-144 111-96-146-13-58-136-58 135-146 13 111 97-33 143ZM173-120l65-281L20-590l288-25 112-265 112 265 288 25-218 189 65 281-247-149-247 149Zm247-340Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function entrada($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M160-160q-33 0-56.5-23.5T80-240v-120h80v120h640v-480H160v120H80v-120q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H160Zm300-140-56-58 83-82H80v-80h407l-83-82 56-58 180 180-180 180Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function infinito($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M220-260q-92 0-156-64T0-480q0-92 64-156t156-64q37 0 71 13t61 37l68 62-60 54-62-56q-16-14-36-22t-42-8q-58 0-99 41t-41 99q0 58 41 99t99 41q22 0 42-8t36-22l310-280q27-24 61-37t71-13q92 0 156 64t64 156q0 92-64 156t-156 64q-37 0-71-13t-61-37l-68-62 60-54 62 56q16 14 36 22t42 8q58 0 99-41t41-99q0-58-41-99t-99-41q-22 0-42 8t-36 22L352-310q-27 24-61 37t-71 13Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function tempoAdicionar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M440-120q-75 0-140.5-28T185-225q-49-49-77-114.5T80-480q0-75 28-140.5T185-735q49-49 114.5-77T440-840q21 0 40.5 2.5T520-830v82q-20-6-39.5-9t-40.5-3q-118 0-199 81t-81 199q0 118 81 199t199 81q118 0 199-81t81-199q0-11-1-20t-3-20h82q2 11 2 20v20q0 75-28 140.5T695-225q-49 49-114.5 77T440-120Zm112-192L400-464v-216h80v184l128 128-56 56Zm168-288v-120H600v-80h120v-120h80v120h120v80H800v120h-80Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function backup($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M260-160q-91 0-155.5-63T40-377q0-78 47-139t123-78q25-92 100-149t170-57q117 0 198.5 81.5T760-520q69 8 114.5 59.5T920-340q0 75-52.5 127.5T740-160H520q-33 0-56.5-23.5T440-240v-206l-64 62-56-56 160-160 160 160-56 56-64-62v206h220q42 0 71-29t29-71q0-42-29-71t-71-29h-60v-80q0-83-58.5-141.5T480-720q-83 0-141.5 58.5T280-520h-20q-58 0-99 41t-41 99q0 58 41 99t99 41h100v80H260Zm220-280Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function sinoMais($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-500Zm0 420q-33 0-56.5-23.5T400-160h160q0 33-23.5 56.5T480-80Zm240-360v-120H600v-80h120v-120h80v120h120v80H800v120h-80ZM160-200v-80h80v-280q0-83 50-147.5T420-792v-28q0-25 17.5-42.5T480-880q25 0 42.5 17.5T540-820v28q14 4 27.5 8.5T593-772q-15 14-27 30.5T545-706q-15-7-31.5-10.5T480-720q-66 0-113 47t-47 113v280h320v-112q18 11 38 18t42 11v83h80v80H160Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function click($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M80-480v-80h120v80H80Zm136 222-56-58 84-84 58 56-86 86Zm28-382-84-84 56-58 86 86-58 56Zm476 480L530-350l-50 150-120-400 400 120-148 52 188 188-80 80ZM400-720v-120h80v120h-80Zm236 80-58-56 86-86 56 56-84 86Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function seta($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m320-410 79-110h170L320-716v306ZM551-80 406-392 240-160v-720l560 440H516l144 309-109 51ZM399-520Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function toque($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M240-720v173q-45-26-72.5-71.5T140-720q0-83 58.5-141.5T340-920q83 0 141.5 58.5T540-720q0 56-27 101.5T441-547v-173q0-42-30-71t-71-29q-42 0-71 29t-29 71ZM419-80q-28 0-52.5-12T325-126L107-403l19-20q20-21 48-25t52 11l74 45v-328q0-17 11.5-28.5T340-760q17 0 29 11.5t12 28.5v472l-97-60 104 133q6 7 14 11t17 4h221q33 0 56.5-23.5T720-240v-160q0-17-11.5-28.5T680-440H461v-80h219q50 0 85 35t35 85v160q0 66-47 113T640-80H419Zm83-260Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function enviar($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M120-160v-640l760 320-760 320Zm80-120 474-200-474-200v140l240 60-240 60v140Zm0 0v-400 400Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function notificacao($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M80-560q0-100 44.5-183.5T244-882l47 64q-60 44-95.5 111T160-560H80Zm720 0q0-80-35.5-147T669-818l47-64q75 55 119.5 138.5T880-560h-80ZM160-200v-80h80v-280q0-83 50-147.5T420-792v-28q0-25 17.5-42.5T480-880q25 0 42.5 17.5T540-820v28q80 20 130 84.5T720-560v280h80v80H160Zm320-300Zm0 420q-33 0-56.5-23.5T400-160h160q0 33-23.5 56.5T480-80ZM320-280h320v-280q0-66-47-113t-113-47q-66 0-113 47t-47 113v280Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function link($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M440-280H280q-83 0-141.5-58.5T80-480q0-83 58.5-141.5T280-680h160v80H280q-50 0-85 35t-35 85q0 50 35 85t85 35h160v80ZM320-440v-80h320v80H320Zm200 160v-80h160q50 0 85-35t35-85q0-50-35-85t-85-35H520v-80h160q83 0 141.5 58.5T880-480q0 83-58.5 141.5T680-280H520Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function link_off($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m770-302-60-62q40-11 65-42.5t25-73.5q0-50-35-85t-85-35H520v-80h160q83 0 141.5 58.5T880-480q0 57-29.5 105T770-302ZM634-440l-80-80h86v80h-6ZM792-56 56-792l56-56 736 736-56 56ZM440-280H280q-83 0-141.5-58.5T80-480q0-69 42-123t108-71l74 74h-24q-50 0-85 35t-35 85q0 50 35 85t85 35h160v80ZM320-440v-80h65l79 80H320Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function arroba($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480v58q0 59-40.5 100.5T740-280q-35 0-66-15t-52-43q-29 29-65.5 43.5T480-280q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-680q83 0 141.5 58.5T680-480v58q0 26 17 44t43 18q26 0 43-18t17-44v-58q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93h200v80H480Zm0-280q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function hub($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M240-40q-50 0-85-35t-35-85q0-50 35-85t85-35q14 0 26 3t23 8l57-71q-28-31-39-70t-5-78l-81-27q-17 25-43 40t-58 15q-50 0-85-35T0-580q0-50 35-85t85-35q50 0 85 35t35 85v8l81 28q20-36 53.5-61t75.5-32v-87q-39-11-64.5-42.5T360-840q0-50 35-85t85-35q50 0 85 35t35 85q0 42-26 73.5T510-724v87q42 7 75.5 32t53.5 61l81-28v-8q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35q-32 0-58.5-15T739-515l-81 27q6 39-5 77.5T614-340l57 70q11-5 23-7.5t26-2.5q50 0 85 35t35 85q0 50-35 85t-85 35q-50 0-85-35t-35-85q0-20 6.5-38.5T624-232l-57-71q-41 23-87.5 23T392-303l-56 71q11 15 17.5 33.5T360-160q0 50-35 85t-85 35ZM120-540q17 0 28.5-11.5T160-580q0-17-11.5-28.5T120-620q-17 0-28.5 11.5T80-580q0 17 11.5 28.5T120-540Zm120 420q17 0 28.5-11.5T280-160q0-17-11.5-28.5T240-200q-17 0-28.5 11.5T200-160q0 17 11.5 28.5T240-120Zm240-680q17 0 28.5-11.5T520-840q0-17-11.5-28.5T480-880q-17 0-28.5 11.5T440-840q0 17 11.5 28.5T480-800Zm0 440q42 0 71-29t29-71q0-42-29-71t-71-29q-42 0-71 29t-29 71q0 42 29 71t71 29Zm240 240q17 0 28.5-11.5T760-160q0-17-11.5-28.5T720-200q-17 0-28.5 11.5T680-160q0 17 11.5 28.5T720-120Zm120-420q17 0 28.5-11.5T880-580q0-17-11.5-28.5T840-620q-17 0-28.5 11.5T800-580q0 17 11.5 28.5T840-540ZM480-840ZM120-580Zm360 120Zm360-120ZM240-160Zm480 0Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function contato($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M160-40v-80h640v80H160Zm0-800v-80h640v80H160Zm320 400q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35ZM160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H160Zm70-80q45-56 109-88t141-32q77 0 141 32t109 88h70v-480H160v480h70Zm118 0h264q-29-20-62.5-30T480-280q-36 0-69.5 10T348-240Zm132-280q-17 0-28.5-11.5T440-560q0-17 11.5-28.5T480-600q17 0 28.5 11.5T520-560q0 17-11.5 28.5T480-520Zm0 40Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function satelite($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M560-32v-80q117 0 198.5-81.5T840-392h80q0 75-28.5 140.5t-77 114q-48.5 48.5-114 77T560-32Zm0-160v-80q50 0 85-35t35-85h80q0 83-58.5 141.5T560-192ZM222-57q-15 0-30-6t-27-17L23-222q-11-12-17-27t-6-30q0-16 6-30.5T23-335l127-127q23-23 57-23.5t57 22.5l50 50 28-28-50-50q-23-23-23-56t23-56l57-57q23-23 56.5-23t56.5 23l50 50 28-28-50-50q-23-23-23-56.5t23-56.5l127-127q12-12 27-18t30-6q15 0 29.5 6t26.5 18l142 142q12 11 17.5 25.5T895-730q0 15-5.5 30T872-673L745-546q-23 23-56.5 23T632-546l-50-50-28 28 50 50q23 23 22.5 56.5T603-405l-56 56q-23 23-56.5 23T434-349l-50-50-28 28 50 50q23 23 22.5 57T405-207L278-80q-11 11-25.5 17T222-57Zm0-79 42-42-142-142-42 42 142 142Zm85-85 42-42-142-142-42 42 142 142Zm184-184 56-56-142-142-56 56 142 142Zm198-198 42-42-142-142-42 42 142 142Zm85-85 42-42-142-142-42 42 142 142ZM448-504Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function inverterCores($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M480-120q-133 0-226.5-92.5T160-436q0-66 25-122t69-100l226-222 226 222q44 44 69 100t25 122q0 131-93.5 223.5T480-120Zm0-80v-568L310-600q-35 33-52.5 74.5T240-436q0 97 70 166.5T480-200Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function cartaoDeCredito($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M880-720v480q0 33-23.5 56.5T800-160H160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720Zm-720 80h640v-80H160v80Zm0 160v240h640v-240H160Zm0 240v-480 480Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function cartaoDeCredito_off($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m871-203-71-71v-206H594L434-640h366v-80H354l-80-80h526q33 0 56.5 23.5T880-720v480q0 10-2 19.5t-7 17.5ZM385-462Zm192-35Zm-211 17H160v240h446L366-480ZM818-28 686-160H160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800l80 80h-80v80h46L26-820l57-57L875-85l-57 57Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function banco($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M200-280v-280h80v280h-80Zm240 0v-280h80v280h-80ZM80-120v-80h800v80H80Zm600-160v-280h80v280h-80ZM80-640v-80l400-200 400 200v80H80Zm178-80h444-444Zm0 0h444L480-830 258-720Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function telaCheia($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M120-120v-200h80v120h120v80H120Zm520 0v-80h120v-120h80v200H640ZM120-640v-200h200v80H200v120h-80Zm640 0v-120H640v-80h200v200h-80Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function ordemAlfabetica($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="m80-280 150-400h86l150 400h-82l-34-96H196l-32 96H80Zm140-164h104l-48-150h-6l-50 150Zm328 164v-76l202-252H556v-72h282v76L638-352h202v72H548ZM360-760l120-120 120 120H360ZM480-80 360-200h240L480-80Z"/></svg>';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }

    public static function exemplo($tipo_return_icon = 'echo')
    {
        $icon_func_mostrar = '';
        $icon_func_mostrar = str_replace('<svg ', '<svg class="classIcons" ', $icon_func_mostrar);
        if ($tipo_return_icon == 'echo') {
            echo $icon_func_mostrar;
        } else {
            return $icon_func_mostrar;
        }
    }
}
