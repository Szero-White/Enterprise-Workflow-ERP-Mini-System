@extends('layouts.app')

@section('page_title', $pageTitle)
@section('page_eyebrow', $pageEyebrow)

@section('content')
<div class="content-card p-4 p-lg-4 erp-form-card">
    <div class="mb-4">
        <h2 class="erp-section-title">{{ $heading }}</h2>
        <p class="erp-section-subtitle mb-0">{{ $subtitle }}</p>
    </div>

    <form method="POST" action="{{ $formAction }}">
        @csrf
        @isset($formMethod)
            @method($formMethod)
        @endisset
        @include($formPartial)
    </form>
</div>
@endsection
