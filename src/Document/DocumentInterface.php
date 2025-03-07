<?php

declare(strict_types=1);

namespace Valantic\ElasticaBridgeBundle\Document;

use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Listing\AbstractListing;
use Valantic\ElasticaBridgeBundle\Enum\DocumentType;
use Valantic\ElasticaBridgeBundle\Index\IndexInterface;

/**
 * Describes how a Pimcore element relates to an Elasticsearch in the context of this index.
 *
 * @template TElement of AbstractElement
 */
interface DocumentInterface
{
    /**
     * Every Elasticsearch document will contain a __type field, corresponding to self::getType().
     */
    public const META_TYPE = '__type';
    /**
     * Every Elasticsearch document will contain a __subType field, corresponding to self::getSubType().
     */
    public const META_SUB_TYPE = '__subType';
    /**
     * Every Elasticsearch document will contain an __id field, corresponding to self::getPimcoreId().
     */
    public const META_ID = '__id';
    /**
     * The localized attributes generated by DataObjectNormalizerTrait::localizedAttributes() will be added to this field.
     *
     * @see DataObjectNormalizerTrait::localizedAttributes()
     */
    public const ATTRIBUTE_LOCALIZED = 'localized';
    /**
     * The array of children IDs generated by DataObjectNormalizerTrait::children() will be added to this field.
     *
     * @see DataObjectNormalizerTrait::children()
     */
    public const ATTRIBUTE_CHILDREN = 'children';
    /**
     * The array of children IDs generated by DataObjectNormalizerTrait::childrenRecursive() will be added to this field.
     *
     * @see DataObjectNormalizerTrait::childrenRecursive()
     */
    public const ATTRIBUTE_CHILDREN_RECURSIVE = 'childrenRecursive';
    /**
     * The array of related object IDs generated by DocumentNormalizerTrait will be added to this field.
     *
     * @see DocumentNormalizerTrait::$relatedObjects
     */
    public const ATTRIBUTE_RELATED_OBJECTS = 'relatedObjects';

    /**
     * Defines the Pimcore type of this document.
     */
    public function getType(): DocumentType;

    /**
     * The subtype, e.g. the DataObject class or Document\Page.
     *
     * Returning null will result in all elements of getType() being included.
     *
     * @return ?class-string
     */
    public function getSubType(): ?string;

    /**
     * Returns the normalization of the Pimcore element.
     * This is how the Pimcore element will be stored in the Elasticsearch document.
     *
     * @param TElement $element
     *
     * @return array<mixed>
     *
     * @see DocumentNormalizerTrait
     * @see DocumentRelationAwareDataObjectTrait
     * @see DataObjectNormalizerTrait
     */
    public function getNormalized(AbstractElement $element): array;

    /**
     * Indicates whether a Pimcore element should be indexed.
     * E.g. return false when the element is not published.
     *
     * @param TElement $element
     */
    public function shouldIndex(AbstractElement $element): bool;

    /**
     * @see ListingTrait
     */
    public function getListingInstance(IndexInterface $index): AbstractListing;

    /**
     * Whether Elasticsearch documents should be created for object variants.
     */
    public function treatObjectVariantsAsDocuments(): bool;

    /**
     * Returns the Elasticsearch ID for a Pimcore element.
     *
     * @param TElement $element
     *
     * @return string
     *
     * @internal
     */
    public static function getElasticsearchId(AbstractElement $element): string;
}
