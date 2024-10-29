<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Franco's Ticket</title>

        <style type="text/css">
            * {
                font-family: Verdana, Arial, sans-serif;
            }
            table{
                font-size: medium;
            }
            tfoot tr td{
                font-weight: bold;
                font-size: medium;
            }
            .gray {
                background-color: lightgray
            }
        </style>
    </head>
    <body>
      <table width="100%">
          <tr>
            <td style="text-align: center;">
              <img src="{{ public_path('images/francos.png') }}" alt="" width="100"/>
            </td>
          </tr>
            <tr>
                <td align="center">
                    <h3>Franco's</h3>
                </td>
            </tr>
            <tr>
                <td align="center" style="border: 1px solid; border-style: dashed solid dashed solid;">
                    <h3> {{ $record->number }} </h3>
                </td>
            </tr>
        </table>

        <br>

        <table width="100%">
            <tr>
              <td align="left"><strong>Fecha generado:</strong> {{ $record->created_at->isoFormat('D, MMM h:mm:ss a') }}</td>
            </tr>
        </table>

        @if($record->address_id)
            <table width="100%">
                <tr>
                  <td align="left"><strong>Dirección:</strong> {{ optional($record->order_address)->full_address }}</td>
                </tr>
            </table>
        @endif

        @if($record->shipping_price)
            <table width="100%">
                <tr>
                  <td align="left"><strong>Envío:</strong> ${{ $record->shipping_price }}</td>
                </tr>
            </table>
        @endif

    	@if($record->shop_customer_id)
        <table width="100%">
            <tr>
              <td align="left"><strong>@lang('Customer'):</strong> {{ $record->customer->name }}</td>
              <td align="left"><strong>@lang('Customer phone'):</strong> {{ $record->customer->phone }}</td>
            </tr>
        </table>
        @endif

        <br>
        <table width="100%">
            <tr>
              <td align="center"><h2><strong>Total:</strong> ${{ number_format($record->total_order, 2, '.', ',') }}</h2></td>
            </tr>
        </table>


        <br>

        @if(count($record->pizzas))
            <table width="100%">
                <thead style="background-color: gray;">
                  <tr align="center">
                  	<th colspan="4" style="color: white;">Pizzas</th>
                  </tr>
                </thead>
                <thead style="background-color: gray; color: white;">
                  <tr align="center">
                      <th scope="col">Cant.</th>
                      <th scope="col">@lang('Size')</th>
                      <th scope="col">@lang('Tipo')</th>
                      <th scope="col">@lang('Total')</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($record->pizzas as $pizza)
                  <tr>
                      <th scope="row" style="text-align: center; border: 1px solid red;">{{ $pizza->quantity }}</th>
                      <td style="text-align: center; border: 1px solid red;">{{ __($pizza->size) }}</td>
                      <td style="text-align: center; border: 1px solid red;">
                        {{ __($pizza->choose) }}
                      </td>
                      <td style="text-align: center; border: 1px solid red;">
                        ${{ __($pizza->unit_price) }}
                      </td>
                  </tr>
                  <tr>
					  <td colspan="3">
@if (is_array($pizza->properties) && count($pizza->properties) > 0)
@if(isset($pizza->properties['speciality_id']))

    @php
        $speciality = App\Models\Shop\Speciality::find($pizza->properties['speciality_id']);
    @endphp
    <br>
    <b>@lang('Speciality'):</b> {{ $speciality->name ?? 'No disponible' }}<br />
    <b>@lang('Ingredients'):</b>
    <ul>
        @foreach ($pizza->properties['ingredients'] as $ingredientId)
            @php
                $ingredient = App\Models\Shop\Ingredient::find($ingredientId); // O usar el método adecuado para cargar el ingrediente
            @endphp
            <li>{{ $ingredient->name ?? 'No disponible' }}</li>
        @endforeach
    </ul>
@endif

@if(isset($pizza->properties['speciality_id_second']))
    @php
        $speciality_second = App\Models\Shop\Speciality::find($pizza->properties['speciality_id_second']);
    @endphp
    <br>
    <b>@lang('Speciality'):</b> {{ $speciality_second->name ?? 'No disponible' }}<br />
    <b>@lang('Ingredients'):</b>
    <ul>
        @foreach ($pizza->properties['ingredients_second'] as $ingredientIdSecond)
            @php
                $ingredientSecond = App\Models\Shop\Ingredient::find($ingredientIdSecond); // O usar el método adecuado para cargar el ingrediente
            @endphp
            <li>{{ $ingredientSecond->name ?? 'No disponible' }}</li>
        @endforeach
    </ul>
@endif

@else
    <p>No hay propiedades disponibles.</p>
@endif
					  </td>                      
				   </tr>
                  @endforeach
                </tbody>
            </table>
            <br>
        @endif

        @if(count($record->items))
            <table width="100%">
                <thead style="background-color: gray;">
                  <tr align="center">
                  	<th colspan="3" style="color: white;">Otros</th>
                  </tr>
                </thead>
                <thead style="background-color: gray; color: white;">
                  <tr align="center">
                      <th scope="col">Cant.</th>
                      <th scope="col">@lang('Description')</th>
                      <th scope="col">@lang('Total')</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($record->items as $item)
	                  <tr>
	                      <th scope="row">{{ $item->qty }}</th>
	                      <td>{{ optional($item->product)->name }}</td>
	                      <td>
	                        ${{ number_format(($item->unit_price * $item->qty) ?? 0 , '2', '.', ',') }}
	                      </td>
	                  </tr>
                  @endforeach
                </tbody>
            </table>
            <br>
        @endif

    </body>
</html>