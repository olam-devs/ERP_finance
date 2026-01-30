@extends('layouts.headmaster')
@section('content')
<iframe src="{{ route('accountant.particular-ledger') }}" class="w-full h-screen border-0"></iframe>
@endsection
