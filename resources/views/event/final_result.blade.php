@extends('layouts.welcome_page.app')
@section('content')
    <main id="main" class="main">
        <section class="section contact">
            <div class="row">
                <h1>Предворительные результаты</h1>
                <div class="card">

                    <div class="card-body">
                        <!-- Table with stripped rows -->
                        <h1>мужчины</h1>
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>
                                    <b>Имя
                                </th>
                                <th>Город</th>
                                <th>Суммарные баллы</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($result as $res)
                                @if($res['gender'] == "male")
                                <tr>
                                    <td>{{$res['user_name']}}</td>
                                    <td>{{$res['city']}}</td>
                                    <td>{{$res['final_points']}}</td>
                                </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                        <h1>женщины</h1>
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>
                                    <b>Имя
                                </th>
                                <th>Город</th>
                                <th>Суммарные баллы</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($result as $res)
                                @if($res['gender'] == "female")
                                    <tr>
                                        <td>{{$res['user_name']}}</td>
                                        <td>{{$res['city']}}</td>
                                        <td>{{$res['final_points']}}</td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                        <!-- End Table with stripped rows -->

                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
