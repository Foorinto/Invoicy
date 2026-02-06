<x-mail::message>
# @if($level === 1)
Rappel de paiement
@elseif($level === 2)
Relance de paiement
@else
Mise en demeure
@endif

{{ $customMessage }}

## Détails de la facture

| | |
|:--|--:|
| **Numéro** | {{ $invoice->number }} |
| **Date d'émission** | {{ $invoice->issued_at?->format('d/m/Y') }} |
| **Date d'échéance** | {{ $invoice->due_at?->format('d/m/Y') }} |
| **Montant TTC** | {{ number_format($invoice->total_ttc, 2, ',', ' ') }} {{ $invoice->currency }} |
@if($daysOverdue > 0)
| **Retard** | {{ $daysOverdue }} jour{{ $daysOverdue > 1 ? 's' : '' }} |
@endif

## Informations de paiement

@if(!empty($seller['iban']))
- **IBAN:** `{{ $seller['iban'] }}`
@endif
@if(!empty($seller['bic']))
- **BIC:** {{ $seller['bic'] }}
@endif
- **Référence:** {{ $invoice->number }}

---

Cordialement,

**{{ $seller['company_name'] ?? $seller['name'] ?? 'L\'équipe' }}**

@if(!empty($seller['email']))
{{ $seller['email'] }}
@endif
@if(!empty($seller['phone']))
| {{ $seller['phone'] }}
@endif

<x-mail::subcopy>
La facture originale est jointe à cet email au format PDF.
</x-mail::subcopy>
</x-mail::message>
