<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulletin de Notes</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 14px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .title { font-size: 22px; font-weight: bold; text-transform: uppercase; }
        .infos { margin-bottom: 20px; line-height: 1.6; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .table th { background-color: #f4f4f4; font-weight: bold; }
        .total-row { font-weight: bold; background-color: #eaeaea; }
        .footer { text-align: center; margin-top: 50px; font-size: 11px; color: #777; }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">Bulletin de Notes</div>
        <div>Année Académique : {{ $annee_academique }}</div>
    </div>

    <div class="infos">
        <strong>Étudiant :</strong> {{ $etudiant->nom }} <br>
        <strong>Email :</strong> {{ $etudiant->email }} <br>
        <strong>Classe :</strong> {{ $classe_nom }}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Matière</th>
                <th>Détail des Notes (Valeur/Coef)</th>
                <th>Moyenne / 20</th>
            </tr>
        </thead>
        <tbody>
            @foreach($moyennes_par_matiere as $item)
                <tr>
                    <td>{{ $item['code'] }}</td>
                    <td>{{ $item['nom'] }}</td>
                    <td>
                        @foreach($item['notes'] as $note)
                            {{ number_format($note->valeur, 2) }} <small>({{ $note->type }}/x{{ $note->coefficient }})</small>{{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    </td>
                    <td><strong>{{ number_format($item['moyenne'], 2) }}</strong></td>
                </tr>
            @endforeach
            
            <tr class="total-row">
                <td colspan="3" style="text-align: right;">MOYENNE GÉNÉRALE :</td>
                <td>{{ number_format($moyenne_generale, 2) }} / 20</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Généré automatiquement par la Plateforme de Gestion des Notes étudiantes.</p>
        <p>Conçu par Miguel AZIFAN.</p>
    </div>

</body>
</html>