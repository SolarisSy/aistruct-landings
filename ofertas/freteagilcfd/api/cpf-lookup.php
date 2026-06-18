<?php
// CPF lookup stub — returns failure so JS falls back to 'Prezado Destinatário'
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => false]);
