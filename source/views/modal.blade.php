<!-- Broken Links Information Modal -->
@modal(
  [
    'closeButtonText' => 'Close',
    'heading' => $title,
    'id' => 'modal-broken-link',
    'size' => 'sm',
    'padding' => 2,
    'borderRadius' => 'lg',
  ]
)
  {{ $content}}
  @slot('bottom')
    @button([
        'href' => $ctaLink,
        'type' => 'filled',
        'text' => $ctaLabel,
        'icon' => 'open_in_new',
        'size' => 'md',
        'color' => 'primary',
        'reverseIcon' => true
    ])
    @endbutton
  @endslot
@endmodal