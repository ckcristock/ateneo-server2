<style>
    * {
        font-family: 'Roboto', sans-serif
    }

    .page-content {
        width: 750px;
    }

    .row {
        display: inline-block;
        width: 100%;
    }

    table.table-border,
    .table-border th,
    .table-border td {
        border: 1px solid;
    }

    /*   table {
        border: 1px solid black;
    } */

    td {
        font-size: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    .section {
        padding: 10px;
        width: 100%;
        background-color: gainsboro;
    }

    .td-header {
        font-size: 10px;
        line-height: 20px;
    }

    .titular {
        /*  font-size: 11px; */
        text-transform: uppercase;
        /*   margin-bottom: 0; */
    }

    .blocks {
        width: 25%;
        display: inline-block;
        text-transform: uppercase;
    }

    .blocks-50 {
        width: 50%;
        display: inline-block;
        text-transform: uppercase;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center
    }

    .text-left {
        text-align: left
    }

    .div {
        width: 100%;
        font-size: 10px;
    }

    .text-uppercase {
        text-transform: uppercase;
    }
</style>
@include('components/cabecera', [$company, $datosCabecera, $image])
<table class="div">
    <thead>
        <tr class="text-left text-uppercase">
            <th class="text-left">Proveedor</th>
            <th class="text-left">Bodega</th>
            <th class="text-left">Fecha de Compra</th>
            <th class="text-left">Fecha Probable de Entrega</th>
        </tr>
    </thead>
    <tbody>
        <tr class="text-left">
            <td>
                {{ optional($data->third)->full_name }}
            </td>
            <td>
                {{ optional($data->store)->Nombre }}
            </td>
            <td>
                {{ $data->created_at }}
            </td>
            <td>
                {{ $data->Fecha_Entrega_Probable }}
            </td>
        </tr>
    </tbody>
</table>

<h6 class="mb-0">Productos</h6>
<table class="div table-border">
    <thead>
        <tr class="text-center text-uppercase" style="background:#E1EEC0;">
            <th>#</th>
            <th>Producto</th>
            <th>Embalaje</th>
            <th>Cantidad</th>
            <th>Costo</th>
            <th>IVA %</th>
            <th>SubTotal</th>
            <th>IVA $</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>

        @foreach ($data['products'] as $key => $producto)
            <tr class="text-center">
                <td>{{ $key + 1 }}</td>
                <td class="align-middle">
                    {{ optional($producto->product)->Nombre_Comercial }}
                </td>
                <td class="align-middle">
                    <span class="text-muted">
                        {{ optional(optional(optional($producto->product))->packaging)->name }}
                    </span>
                </td>
                <td class="align-middle">
                    {{ $producto->Cantidad }}
                </td>
                <td class="align-middle">
                    @money(optional($producto->product)->Precio)
                </td>
                <td class="align-middle">
                    {{ optional($producto->tax)->Valor }}%
                </td>
                <td class="align-middle">
                    @money($producto->Subtotal)
                </td>
                <td class="align-middle">
                    @money($producto->Valor_Iva)
                </td>
                <td class="align-middle">
                    @money($producto->Total)
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<table class="div mt-2">
    <thead>
        <tr class="text-uppercase">
            <th class="text-left">Observaciones</th>
        </tr>
    </thead>
    <tbody>
        <tr class="text-left">
            <td>{{ $data->Observaciones }}</td>
        </tr>
    </tbody>
</table>

<div class="row">
    <div class="text-right" style="font-size: 10px;">
        <strong>SUBTOTAL: </strong>
        @money($data['Subtotal'])
    </div>
    <div class="text-right" style="font-size: 10px;">
        <strong>IVA: </strong>
        @money($data['Iva'])
    </div>
    <div class="text-right" style="font-size: 10px;">
        <strong>TOTAL: </strong>
        @money($data['Total'])
    </div>
</div>
