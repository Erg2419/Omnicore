<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>BuildSmart — Gestión de Proyectos</title>

  <!-- Tailwind y fuente moderna -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f9fafb;
      color: #1f2937;
      transition: all .3s ease;
    }

    /* Sidebar */
    #sidebar {
      width: 18rem; /* ancho por defecto */
      background: white;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
      overflow-y: auto;
      transition: all 0.3s ease;
    }

    #sidebar::-webkit-scrollbar {
      width: 6px;
    }
    #sidebar::-webkit-scrollbar-thumb {
      background-color: rgba(249,115,22,0.4);
      border-radius: 10px;
    }

    .sidebar-link {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      border-radius: 10px;
      font-weight: 500;
      transition: all .3s ease;
      color: #4b5563;
      font-size: 0.9rem;
      white-space: nowrap;
    }

    .sidebar-link:hover {
      background: rgba(249,115,22,0.1);
      color: #f97316;
      transform: translateX(3px);
    }

    .active {
      background: linear-gradient(to right, #f97316, #fb923c);
      color: white;
      font-weight: 600;
      box-shadow: 0 2px 6px rgba(249,115,22,0.3);
    }

    .logo-section {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 20px 10px;
      border-bottom: 1px solid #f3f4f6;
      background: linear-gradient(to right, #fff7ed, #ffffff);
      cursor: pointer;
    }

    .logo-section h1 {
      color: #f97316;
      font-weight: 700;
      font-size: 1.25rem;
      margin-top: 5px;
    }

    /* Contenido principal */
    #mainContent {
      transition: margin-left 0.3s;
    }

    @media(min-width:768px){
      #mainContent {
        margin-left: 18rem; /* ancho del sidebar */
      }
    }
  </style>
</head>
<body class="bg-[#f9fafb] text-gray-800 min-h-screen">

<!-- HEADER -->
<header class="flex items-center justify-between bg-white border-b p-4 shadow-sm">
  <div class="flex items-center gap-2">
    <h1 class="text-xl font-bold">Panel de Administración</h1>
  </div>

  <div class="flex items-center gap-2 text-gray-600">
    <i class="fa-solid fa-user text-purple-600"></i>
    <span>Administrador General</span>
  </div>
</header>

<!-- Incluimos el sidebar -->
<?php include __DIR__ . '/sidebar.php'; ?>

<!-- CONTENIDO PRINCIPAL -->
<main id="mainContent" class="flex-1 flex flex-col transition-all duration-300">
  <section class="flex-1 overflow-y-auto p-8">
