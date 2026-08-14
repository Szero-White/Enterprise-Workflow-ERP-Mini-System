@extends('layouts.app')

@section('page_title', $pageTitle)
@section('page_eyebrow', $pageEyebrow)

@section('content')
<x-erp.page-header :title="$heading" :eyebrow="$pageEyebrow" :description="$subtitle" />

<div class="erp-form-card">
    <section class="erp-form-section">
        <form method="POST" action="{{ $formAction }}">
            @csrf
            @isset($formMethod)
                @method($formMethod)
            @endisset
            @include($formPartial)
        </form>
    </section>
</div>
@endsection
