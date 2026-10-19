/**
 * Bloc « région » dans l'éditeur : affiche le nom de la région et n'accepte que les sections
 * prévues par structure.yml, dans la limite de _max (window.carloRegions, cf. inc/regions.php).
 * Écrit sans JSX pour se passer d'étape de build.
 */
(function (wp, regions) {
  const el = wp.element.createElement;
  const { useBlockProps, useInnerBlocksProps, InnerBlocks } = wp.blockEditor;
  const { useSelect } = wp.data;

  wp.blocks.registerBlockType('carlo/region', {
    edit({ attributes, clientId }) {
      const region = regions[attributes.region] || { label: attributes.region, allowedBlocks: [], max: null };
      const count = useSelect((select) => select('core/block-editor').getBlockCount(clientId), [clientId]);
      const full = region.max !== null && count >= region.max;

      const blockProps = useBlockProps({ className: 'carlo-region' });
      // Une région pleine n'accepte plus aucun bloc : ni ajout, ni duplication, ni collage.
      const innerBlocksProps = useInnerBlocksProps(
        { className: 'carlo-region__blocks' },
        {
          allowedBlocks: full ? [] : region.allowedBlocks,
          templateLock: false,
          renderAppender: full ? false : InnerBlocks.ButtonBlockAppender,
        }
      );

      return el(
        'div',
        blockProps,
        el(
          'div',
          { className: 'carlo-region__header' },
          el('span', null, region.label),
          region.max !== null &&
            el(
              'span',
              { className: 'carlo-region__count', 'aria-live': 'polite' },
              `${count} / ${region.max} blocs${full ? ' (maximum atteint)' : ''}`
            )
        ),
        el('div', innerBlocksProps)
      );
    },
    save: () => el(InnerBlocks.Content),
  });
})(window.wp, window.carloRegions || {});
