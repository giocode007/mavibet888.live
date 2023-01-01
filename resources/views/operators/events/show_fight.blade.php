@extends('layouts.player')
@push('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush
@section('content')
<div id="main">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-first">
                    <h3 class="text-white"><a href="{{ url('arena/'.$selectedEvent[0]->id) }}">{{ \Carbon\Carbon::parse($selectedEvent[0]->fight_date_time)->isoFormat('dddd') }} {{ $selectedEvent[0]->event_name }} - {{ \Carbon\Carbon::parse($selectedEvent[0]->fight_date_time)->isoFormat('MM/DD/Y') }}</a></h3>
                    <p class="text-subtitle font-bold text-muted">Fight number # <span class="text-warning">{{ $selectedFight[0]->fight_number }}</span>  bets information list</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-last">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="/events">Events</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><a href="{{ url('events/'.$selectedEvent[0]->id) }}">Fights</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Bets</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        {{-- message --}}
        {!! Toastr::message() !!}
        <section class="section">
            <div class="card">
                {{-- <div class="card-header">
                    <a href="javascript:void(0)" id="create-new-post">
                        <span class="p-3 badge bg-success"><i class="icon-mid bi bi-plus-circle me-2"></i>ADD EVENT</span>
                    </a>
                </div> --}}
                <div class="card-body">
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th>Local Date</th>
                                <th>Username</th>
                                <th>Bet On</th>
                                <th>Bet Result</th>
                                <th>Bet Amount</th>
                                <th>Palasada</th>
                                <th>Gross Winning</th>
                                <th>Net</th>
                                <th>Current Balance</th>
                            </tr>    
                        </thead>
                        <tbody id="event-crud">
                            @foreach ($allBets as $allBet)
                                <tr id="fight_id_{{ $allBet['id'] }}">
                                    <td class="name">{{ $allBet['local_date'] }}</td>
                                    <td class="name">{{ $allBet['user_name'] }}</td>
                                    @if ($allBet['bet'] == 'meron')
                                        <td class="name font-bold text-danger" style="text-transform:uppercase;">{{ $allBet['bet']  }}</td>
                                    @elseif ($allBet['bet']  == 'wala')
                                        <td class="name font-bold text-primary" style="text-transform:uppercase;">{{ $allBet['bet']  }}</td>
                                    @elseif ($allBet['bet']  == 'draw')
                                        <td class="name font-bold text-success" style="text-transform:uppercase;">{{ $allBet['bet']  }}</td>
                                    @else 
                                        <td class="name font-bold text-light-secondary" style="text-transform:uppercase;">{{ $allBet['bet']  }}</td>
                                    @endif
                                    @if ($allBet['result'] == 'meron')
                                        <td class="name font-bold text-danger" style="text-transform:uppercase;">{{ $allBet['result'] }}</td>
                                    @elseif ($allBet['result'] == 'wala')
                                        <td class="name font-bold text-primary" style="text-transform:uppercase;">{{ $allBet['result'] }}</td>
                                    @elseif ($allBet['result'] == 'draw')
                                        <td class="name font-bold text-success" style="text-transform:uppercase;">{{ $allBet['result'] }}</td>
                                    @elseif ($allBet['result'] == 'cancel')
                                        <td class="name font-bold text-light-secondary" style="text-transform:uppercase;">{{ $allBet['result'] }}</td>
                                    @else
                                        <td> - </td>
                                    @endif
                                    <td class="name">{{ $allBet['amount'] }}</td>
                                    @if ($allBet['isWin'] == 1)
                                        <td class="name">{{ $allBet['palasada'] }}</td>
                                        <td class="name bg-primary text-white text-center">{{ $allBet['gross_winning'] }}</td>
                                        <td class="name">{{ $allBet['net'] }}</td>
                                    @elseif($allBet['isWin'] == 0)
                                        <td> - </td>
                                        <td> - </td>
                                        <td> - </td>
                                    @else
                                        <td class="name">{{ $allBet['palasada'] }}</td>
                                        <td class="name bg-danger text-white  text-center"> {{ $allBet['gross_winning'] }}</td>
                                        <td> - </td>
                                    @endif
                                    @if($allBet['role_type'] == 'Operator' || $allBet['role_type'] == 'Declarator')
                                        <td class="name bg-danger text-white">{{ $allBet['current_balance'] }}</td>
                                    @else
                                        <td class="name">{{ $allBet['current_balance'] }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>


@push('scripts')
<script>
    // Simple Datatable
    let table1 = document.querySelector('#table1');
    let dataTable = new simpleDatatables.DataTable(table1);
</script>
  
@endpush
@endsection
