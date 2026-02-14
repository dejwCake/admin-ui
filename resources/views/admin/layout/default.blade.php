@extends('brackets/admin-ui::admin.layout.master')

@section('content')

    @if(View::exists('admin.layout.sidebar'))
        @include('admin.layout.sidebar')
    @endif

    <div class="wrapper d-flex flex-column min-vh-100">

        @include('brackets/admin-ui::admin.partials.header')

        <div class="body flex-grow-1">

            <div class="container-fluid" id="app" :class="{'loading': loading}">
                <div class="modals">
                    <v-dialog/>
                </div>
                <div>
                    <notifications position="bottom right" :duration="2000" />
                </div>

                @yield('body')
            </div>
        </div>

        @include('brackets/admin-ui::admin.partials.footer')
    </div>
@endsection

@section('bottom-scripts')
    @parent
@endsection
