@extends('layouts.headmaster')
@section('content')
<iframe src="{{ route('accountant.invoices-page') }}" class="w-full h-screen border-0"></iframe>
@endsection
