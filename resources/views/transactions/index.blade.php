@extends('layouts.app')

@section('content')
@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif
<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold mb-4">Visão Geral Financeira</h1>
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-green-100 p-4 rounded-lg shadow">
            <p class="text-sm text-green-700">Receitas</p>
            <p class="text-2xl font-semibold text-green-800">
                R$ {{ number_format($transactions->where('amount', '>=', 0)->sum('amount'), 2, ',', '.') }}
            </p>
        </div>
        <div class="bg-red-100 p-4 rounded-lg shadow">
            <p class="text-sm text-red-700">Despesas</p>
            <p class="text-2xl font-semibold text-red-800">
                R$ {{ number_format($transactions->where('amount', '<', 0)->sum('amount') * -1, 2, ',', '.') }}
            </p>
        </div>
    </div>

    <h2 class="text-xl font-semibold mb-2">Transações</h2>
    <a href="{{ route('transaction.create') }}" class="inline-block mb-4 text-blue-600 hover:underline">+ Nova Transação</a>

    <div class="overflow-x-auto">
        <table class="w-full table-auto border-collapse">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="p-2">Descrição</th>
                    <th class="p-2">Categoria</th>
                    <th class="p-2">Valor</th>
                    <th class="p-2">Data</th>
                    <th class="p-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $transaction)
                    <tr class="border-b">
                        <td class="p-2">{{ $transaction->description }}</td>
                        <td class="p-2">{{ $transaction->category }}</td>
                        <td class="p-2 {{ $transaction->amount < 0 ? 'text-red-600' : 'text-green-600' }}">
                            R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                        </td>
                        <td class="p-2">{{ $transaction->date->format('d/m/Y') }}</td>
                        <td class="p-2">
                            <a href="{{ route('transaction.edit', $transaction) }}" class="text-blue-600 hover:underline">Editar</a>
                            <form action="{{ route('transaction.destroy', $transaction->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta transação?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
