@php($is403Breadcrumb = true)
@extends('layouts.admin')

@section('content')
    <div class="error-403-container">
        <div class="error-403-flex">
            <div class="error-403-left">
                <span class="error-text-code">ERROR</span>
                <span class="error-403-code">403</span>
            </div>
            <div class="error-403-right">
                <h1 class="error-403-title">Acceso Denegado</h1>
                <p class="error-403-message">
                    No tienes permisos para realizar esta acción.<br>
                    Por favor, contacta al administrador si crees que esto es un error.
                </p>
                <a href="{{ route('admin.dashboard') }}" class="boton-form boton-primary py-3">
                    <span class="boton-form-icon"><i class="ri-home-smile-2-fill"></i></span>
                    <span class="boton-form-text">Volver al inicio</span>
                </a>
            </div>
        </div>
    </div>
@endsection
