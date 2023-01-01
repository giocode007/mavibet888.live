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
                    <h3 class="text-white">User Logs</h3>
                    <p class="text-subtitle text-muted">Player logs information list</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-last">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        @if ($user->role_type == 'Player')
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('getAgents') }}">Agents</a></li>
                            <li class="breadcrumb-item"><a href="#" onclick="history.back()">Players</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Logs</li>
                        </ol>
                        @else
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#" onclick="history.back()">Agents</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Logs</li>
                        </ol>
                        @endif
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
                                <th>Description</th>
                                <th>Date</th>
                            </tr>    
                        </thead>
                        <tbody id="event-crud">
                            @foreach ($logs as $log)
                                <tr id="fight_id_{{ $log->id }}">
                                    <td class="name">{{ $log->description }}</td>
                                    <td class="name">{{ ($log->date_time) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

{{-- MODAL --}}
<div class="modal fade w-100" id="ajax-crud-modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="fightModal"></h4>
        </div>
        <div class="modal-body">
            <form id="fightForm" name="fightForm" class="form-horizontal">
               <input type="hidden" name="fightId" id="fightId">
                <div class="form-group">
                    <label for="fight_name" class="col-sm-2 control-label">FIGHT NUMBER</label>
                    <div class="col-sm-12">
                        <input disabled="disabled" class="form-control" id="fight_name" name="fight_name" value="" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label for="result" class="col-sm-4 control-label">FIGHT RESULT</label>
                    <select class="form-control" id="result" name="result" value="">
                        <option value="meron">MERON</option>
                        <option value="wala">WALA</option>
                        <option value="draw">DRAW</option>
                        <option value="cancel">CANCEL</option>
                    </select>
                </div>

                <div class="col-sm-offset-2 col-sm-10">
                 <button type="submit" class="btn btn-warning" id="btn-save">Reverse
                 </button>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            
        </div>
    </div>
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
