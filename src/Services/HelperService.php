<?php
namespace PSinfoodservice\Services;
 
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\Exceptions\PSApiException; 
use PSinfoodservice\Domain\Language;
use PSinfoodservice\Domain\Outputstyle;

class HelperService
{
    /**
     * CDN base URL for allergen images.
     */
    private string $cdn;

    /**
     * Translation data for multilingual support.
     */
    private array $translations;

    /**
     * SVG icons for different levels of allergen containment.
     */
    private array $levelOfContainment;

    /**
     * Initializes a new instance of the HelperService. 
     */
    public function __construct()
    {
        $this->cdn = "https://cdn.psinfoodservice.com/images/productsheet/allergenen/"; 
        $this->translations = [
            'nl' => [
                'contains' => 'Bevat',
                'may_contain' => 'Kan sporen bevatten',
                'without' => 'Zonder',
                'not_specified' => 'Niet opgegeven',
                'per' => 'Per',
                'per_portie' => 'Per portie'
            ],
            'en' => [
                'contains' => 'Contains',
                'may_contain' => 'May contain traces',
                'without' => 'Without',
                'not_specified' => 'Not specified',
                'per' => 'Per',
                'per_portie' => 'Per portion'
            ],
            'fr' => [
                'contains' => 'Contient',
                'may_contain' => 'Peut contenir des traces',
                'without' => 'Sans',
                'not_specified' => 'Non sp�cifi�',
                'per' => 'Par ',
                'per_portie' => 'Par portion'
            ],
            'de' => [
                'contains' => 'Enth�lt',
                'may_contain' => 'Kann Spuren enthalten',
                'without' => 'Ohne',
                'not_specified' => 'Nicht angegeben',
                'per' => 'Pro',
                'per_portie' => 'Pro Portion'
            ]
        ]; 
        $this->levelOfContainment = [
            '<svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 448 512" class="text-red-500" height="12" width="12" xmlns="http://www.w3.org/2000/svg"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"></path></svg>',
            '<svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 384 512" class="text-red-500" height="12" width="12" xmlns="http://www.w3.org/2000/svg"><path d="M224 32c0-17.7-14.3-32-32-32s-32 14.3-32 32V144H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H160V320c0 17.7 14.3 32 32 32s32-14.3 32-32V208H336c17.7 0 32-14.3 32-32s-14.3-32-32-32H224V32zM0 480c0 17.7 14.3 32 32 32H352c17.7 0 32-14.3 32-32s-14.3-32-32-32H32c-17.7 0-32 14.3-32 32z"></path></svg>',
            '<svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 448 512" height="12" width="12" xmlns="http://www.w3.org/2000/svg"><path d="M432 256c0 17.7-14.3 32-32 32L48 288c-17.7 0-32-14.3-32-32s14.3-32 32-32l352 0c17.7 0 32 14.3 32 32z"></path></svg>',
            '<svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256z"></path></svg>'
        ];
    }

    /**
     * Retrieves ingredient information for a product
     * 
     * @param object $data Product data object
     * @param string $language Language code
     * @return object|null Object with ingredient information or null on error
     */
    public function getIngredientsPreview(object $productSheet, string $language = Language::nl): ?object
    {
        if ($language == Language::all) {
            $language = Language::nl;
        }

        try {
            if($productSheet == null) {
                return null;
            }

            $ingredients = new \stdClass();
            $ingredients->declaration = $this->getLocalizedIngredientValue($productSheet, 'declaration', $language);
            $ingredients->declarationPreview = $this->getLocalizedIngredientValue($productSheet, 'declarationPreview', $language);
            $ingredients->ingredients = $this->getIngredientsList($productSheet, $language);

            return $ingredients;
        } catch (ClientException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new PSApiException(
                $errorResponse['detail'] ?? $errorResponse['title'] ?? 'Unknown error occurred',
                $e->getResponse()->getStatusCode(),
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }
    
    /**
     * Gets a list of ingredients from product data.
     *
     * @param object $data Product data object
     * @param string $language Language code
     * @return array|null Array of ingredients or null if not available
     */
    private function getIngredientsList($data, string $language): ?array
    {
        if (
            !isset($data->specification) ||
            !isset($data->specification->ingredientSet) ||
            !isset($data->specification->ingredientSet->ingredients)
        ) {
            return null;
        }

        $ingredients = [];
        foreach ($data->specification->ingredientSet->ingredients as $ingredient) {
            $ingredients[] = [
                'sequence' => $ingredient->sequence ?? null,
                'name' => $this->getLocalizedValue($ingredient->name ?? [], $language, ''),
                'countryOfOrigins' => $this->getLocalizedCountryOfOrigins($ingredient->countryOfOrigins ?? [], $language)
            ];
        }

        return empty($ingredients) ? null : $ingredients;
    }
    
    /**
     * Gets a localized ingredient value from product data.
     *
     * @param object $data Product data object
     * @param string $property Property name to retrieve
     * @param string $language Language code
     * @return string Localized ingredient value or empty string
     */
    private function getLocalizedIngredientValue($data, string $property, string $language): string
    {
        if (
            !isset($data->specification) ||
            !isset($data->specification->ingredientSet) ||
            !isset($data->specification->ingredientSet->$property) ||
            !is_array($data->specification->ingredientSet->$property)
        ) {
            return '';
        }

        foreach ($data->specification->ingredientSet->$property as $declaration) {
            if (isset($declaration->language) && $declaration->language === $language && isset($declaration->value)) {
                return $declaration->value;
            }
        }

        if (count($data->specification->ingredientSet->$property) > 0) {
            $first = $data->specification->ingredientSet->$property[0];
            return $first->value ?? '';
        }

        return '';
    }

    /**
     * Retrieves nutritional information for a product
     * 
     * @param object $data Product data object
     * @param string $language Language code
     * @param string $style Output style ('table' or 'bootstrap')
     * @return string|null HTML representation of nutritional information or null on error
     */
    public function getNutrientsPreview(object $productSheet, string $language = Language::nl, string $style = Outputstyle::table): ?string {
        if ($language == Language::all) {
            $language = Language::nl;
        }

        try { 
            if ($productSheet == null) {
                return null;
            }

            $stateofpreparationlist = $this->getStateOfPreparationList($productSheet, $language);
            if (empty($stateofpreparationlist) || !is_array($stateofpreparationlist)) {
                return null;
            }

            $nutritionTableHtml = $this->generateNutritionTable($stateofpreparationlist, $language);
            return $nutritionTableHtml;
        } catch (ClientException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new PSApiException(
                $errorResponse['detail'] ?? $errorResponse['title'] ?? 'Unknown error occurred',
                $e->getResponse()->getStatusCode(),
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }
    
    /**
     * Generates HTML for a nutrition table.
     *
     * @param array $stateofpreparationlist List of preparation states with nutrients
     * @param string $language Language code
     * @return string HTML table of nutritional information
     */
    private function generateNutritionTable($stateofpreparationlist, $language)
    {
        if (empty($stateofpreparationlist) || !is_array($stateofpreparationlist)) {
            return '';
        }
        $labels = $this->translations[$language];

        $html = '<table class="nutrition-tabel">';
        $html .= '<thead><tr><th></th>';
         
        foreach ($stateofpreparationlist as $prep) {
            $prepName = isset($prep['name']) ? str_replace(["\r", "\n"], '', (string)$prep['name']) : '';
            $html .= '<th colspan="2">' . htmlspecialchars($prepName) . '</th>';
        }
        $html .= '</tr>';
         
        $html .= '<tr><th></th>';
        foreach ($stateofpreparationlist as $prep) {
            $perHunderdUomName = isset($prep['perHunderdUomName']) ? $prep['perHunderdUomName'] : '';
            $servingUnitValue = isset($prep['servingUnitValue']) ? $prep['servingUnitValue'] : 0;
            $servingUomName = isset($prep['servingUomName']) ? $prep['servingUomName'] : '';
            $html .= '<th>'. $labels['per'].' 100 ' . htmlspecialchars($perHunderdUomName) . '</th>';
            $html .= '<th>'. $labels['per_portie'].' (' . htmlspecialchars((string)$servingUnitValue) . ' ' . htmlspecialchars($servingUomName) . ')</th>';
        }
        $html .= '</tr></thead>'; 
        $html .= '<tbody>';
         
        $html .= $this->generateEnergyRow($stateofpreparationlist);
         
        $allNutrients = [];
        foreach ($stateofpreparationlist as $prep) {
            foreach (($prep['nutrients'] ?? []) as $nutrient) { 
                if (!$this->isEnergyNutrient($nutrient)) { 
                    $allNutrients[$nutrient['id']] = ($nutrient['parentid'] != 0 ? ' - ' : '') . $nutrient['name'];
                }
            }
        }

        $nutrientStructure = [];
        foreach ($stateofpreparationlist as $prep) {
            foreach (($prep['nutrients'] ?? []) as $nutrient) {
                if ($this->isEnergyNutrient($nutrient)) {
                    continue;
                }

                if ($nutrient['parentid'] != 0) {
                    $parentId = null;
                    foreach (($prep['nutrients'] ?? []) as $potentialParent) {
                        if (strpos($nutrient['name'], $potentialParent['name']) !== false && $potentialParent['id'] != $nutrient['id']) {
                            $parentId = $potentialParent['id'];
                            break;
                        }
                    }

                    if ($parentId) {
                        if (!isset($nutrientStructure[$parentId])) {
                            $nutrientStructure[$parentId] = [];
                        }
                        $nutrientStructure[$parentId][$nutrient['id']] = $nutrient['name'];
                    } else {
                        $nutrientStructure[$nutrient['id']] = [];
                    }
                } else {
                    if (!isset($nutrientStructure[$nutrient['id']])) {
                        $nutrientStructure[$nutrient['id']] = [];
                    }
                }
            }
        }

        $generateNutrientRow = function ($nutrientId, $nutrientName) use ($stateofpreparationlist) {
            $html = '<tr>';
            if(str_starts_with($nutrientName, ' - ')) {
                $html .= '<td class="nutrition-subnutrient">' . substr(htmlspecialchars($nutrientName), 3) . '</td>';
            }else{
                $html .= '<td class="nutrition-nutrient">' . htmlspecialchars($nutrientName) . '</td>';
            }
            foreach ($stateofpreparationlist as $prep) {
                $found = false;
                foreach (($prep['nutrients'] ?? []) as $nutrient) {
                    if ($nutrient['id'] == $nutrientId) {
                        $html .= '<td>' . htmlspecialchars($nutrient['value']) . ' ' . htmlspecialchars($nutrient['unitofmeasure']) . '</td>';
                        $html .= '<td>' . htmlspecialchars($nutrient['valueperserving']) . ' ' . htmlspecialchars($nutrient['unitofmeasure']) . '</td>';
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $html .= '<td>-</td><td>-</td>';
                }
            }

            $html .= '</tr>';
            return $html;
        };

        foreach ($nutrientStructure as $nutrientId => $subnutrients) {
            if (isset($allNutrients[$nutrientId])) {
                $html .= $generateNutrientRow($nutrientId, $allNutrients[$nutrientId]);
            }
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Checks if a nutrient is an energy nutrient (kJ/kcal).
     *
     * @param array $nutrient Nutrient data
     * @return bool True if it's an energy nutrient
     */
    private function isEnergyNutrient($nutrient)
    {
        return $nutrient["id"] == 1 || $nutrient["id"] == 2;
    }

    /**
     * Generates the energy row for the nutrition table.
     *
     * @param array $stateofpreparationlist List of preparation states
     * @return string HTML row for energy values
     */
    private function generateEnergyRow($stateofpreparationlist)
    {
        $html = '<tr>';
        if (empty($stateofpreparationlist) || !is_array($stateofpreparationlist)) {
            return '';
        }

        $energyName = 'Energy';
        if (isset($stateofpreparationlist[0]['nutrients']) && is_array($stateofpreparationlist[0]['nutrients'])) {
            foreach ($stateofpreparationlist[0]['nutrients'] as $n) {
                if (isset($n['id']) && ($n['id'] == 1 || $n['id'] == 2) && isset($n['name'])) {
                    $energyName = $n['name'];
                    break;
                }
            }
            if ($energyName === 'Energy' && isset($stateofpreparationlist[0]['nutrients'][0]['name'])) {
                $energyName = $stateofpreparationlist[0]['nutrients'][0]['name'];
            }
        }
        $html .= '<td class="nutrient">'. htmlspecialchars($energyName) .' (kJ/kcal)</td>';

        foreach ($stateofpreparationlist as $prep) {
            $kj = '';
            $kcal = '';
            $kjPerServing = '';
            $kcalPerServing = '';
            foreach (($prep['nutrients'] ?? []) as $nutrient) {
                if ($nutrient['id'] == 1) {
                    $kj = $nutrient['value'];
                    $kjPerServing = $nutrient['valueperserving'];
                } elseif ($nutrient['id'] == 2) {
                    $kcal = $nutrient['value'];
                    $kcalPerServing = $nutrient['valueperserving'];
                }
            }

            if ($kj !== '' && $kcal !== '') {
                $html .= '<td>' . $kj . ' / ' . $kcal . '</td>';
                $html .= '<td>' . $kjPerServing . ' / ' . $kcalPerServing . '</td>';
            } else {
                $html .= '<td>-</td><td>-</td>';
            }
        }

        $html .= '</tr>';
        return $html;
    }

    /**
     * Gets a list of preparation states with nutrients.
     *
     * @param object $data Product data object
     * @param string $language Language code
     * @return array|null Array of preparation states or null if not available
     */
    private function getStateOfPreparationList($data, string $language): ?array
    {
        if (
            !isset($data->specification) ||
            !isset($data->specification->nutrientset) ||
            !isset($data->specification->nutrientset->stateOfPreparations)
        ) {
            return null;
        }

        $stateofpreparations = []; 
        foreach ($data->specification->nutrientset->stateOfPreparations as $stateofpreparation) {
            $stateofpreparations[] = [
                'stateofpreparationid' => $stateofpreparation->stateOfPreparationId, 
                'servingUnitValue' => $stateofpreparation->servingUnitValue ?? 0, 
                'name' => $this->getLocalizedValue($stateofpreparation->stateOfPreparationName ?? [], $language, ''),
                'perHunderdUomName' => $this->getLocalizedValue($stateofpreparation->perHunderdUomName ?? [], $language, ''),
                'servingUomName' => $this->getLocalizedValue($stateofpreparation->servingUomName ?? [], $language, ''), 
                'nutrients' => $this->getNutrientList($stateofpreparation->nutrients ?? [], $language), 
            ]; 
        }
        
        return empty($stateofpreparations) ? null : $stateofpreparations;
    }

    /**
     * Gets a list of nutrients for a preparation state.
     *
     * @param array $data Nutrient data array
     * @param string $language Language code
     * @return array|null Array of nutrients or null if not available
     */
    private function getNutrientList($data, string $language): ?array
    {
        if (
            !isset($data)
        ) {
            return null;
        }

        $nutrients = [];
        foreach ($data as $nutrient) {
            $nutrients[] = [
                'id' => $nutrient->id,
                'parentid' => $nutrient->parentId,
                'name' => $this->getLocalizedValue($nutrient->name ?? [], $language, ''),
                'value' => $nutrient->value ?? 0,
                'valueperserving' => $nutrient->valuePerServing ?? 0,
                'unitofmeasure' => $this->getLocalizedValue($nutrient->unitOfMeasure->name ?? [], $language, ''),
            ];
        }

        return empty($nutrients) ? null : $nutrients;
    }

    /**
     * Retrieves preparation information for a product
     * 
     * @param object $data Product data object
     * @param string $language Language code
     * @return array|null Array of preparation information objects or null on error
     */
    public function getPreparationInformationPreview(object $productSheet, string $language = Language::nl): ?array
    {
        if ($language == Language::all) {
            $language = Language::nl;
        }

        try { 
            if($productSheet == null) {
                return null;
            }

            if (
                !isset($productSheet->specification) ||
                !isset($productSheet->specification->preparationInformations) ||
                !is_array($productSheet->specification->preparationInformations)
            ) {
                return null;
            }

            $preparationInformations = []; 
            foreach ($productSheet->specification->preparationInformations as $information) {
                $preparationInformation = new \stdClass();
                $preparationInformation->preparationType = $this->getLocalizedValue($information->preparationType->name ?? [], $language, '');
                $preparationInformation->description = $this->getLocalizedValue($information->preparationDescription ?? [], $language, '');

                $preparationInformations[] = $preparationInformation;
            }

            return $preparationInformations ?? [];
        } catch (ClientException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new PSApiException(
                $errorResponse['detail'] ?? $errorResponse['title'] ?? 'Unknown error occurred',
                $e->getResponse()->getStatusCode(),
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }

    /**
     * Retrieves allergen information for a product
     * 
     * @param object $data Product data object
     * @param bool $extended Whether extended information is needed
     * @param string $language Language code
     * @param string $style Output style ('table' or 'bootstrap')
     * @return string|null HTML representation of allergens or null on error
     */
    public function getAllergensPreview(object $productSheet, bool $extended, string $language = Language::nl, string $style = Outputstyle::table): ?string
    { 
        if($language == Language::all) {
            $language = Language::nl;
        }

        try { 
            if ($productSheet == null) {
                return null;
            }

            $list = $this->getAllegenList($productSheet, $extended, $language);
             
            usort($list, function ($a, $b) {
                $seqA = $a['sequence'] ?? 999;
                $seqB = $b['sequence'] ?? 999;
                return $seqA - $seqB;
            });
             
            if ($style === Outputstyle::bootstrap) {
                return $this->renderBootstrapAllergens($list, $extended, $language);
            } else {
                return $this->renderTableAllergens($list, $extended, $language);  
            }

        } catch (ClientException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new PSApiException(
                $errorResponse['detail'] ?? $errorResponse['title'] ?? 'Unknown error occurred',
                $e->getResponse()->getStatusCode(),
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }

    /**
     * Gets a list of allergens from product data.
     *
     * @param object $data Product data object
     * @param bool $extended Whether to include sub-allergens
     * @param string $language Language code
     * @return array|null Array of allergens or null if not available
     */
    private function getAllegenList($data, bool $extended, string $language): ?array {
        if (
            !isset($data->specification) ||
            !isset($data->specification->allergenSet) ||
            !isset($data->specification->allergenSet->allergens)
        ) {
            return null;
        }

        $allergens = [];
        if($extended == false) { 
            foreach ($data->specification->allergenSet->allergens as $allergen) {
                if ((isset($allergen->parentId) ? $allergen->parentId : 0) == 0) {
                    $allergens[] = [
                        'id' => $allergen->id,
                        'sequence' => $allergen->sequence ?? null,
                        'name' => $this->getLocalizedValue($allergen->name ?? [], $language, ''),
                        'levelOfContainmentId' => isset($allergen->levelOfContainment->id) ? $allergen->levelOfContainment->id : 0,
                        'levelOfContainment' => $this->getLocalizedValue($allergen->levelOfContainment->name ?? [], $language, '')
                    ];
                }
            }
        } else {
            foreach ($data->specification->allergenSet->allergens as $allergen) {
                $allergens[] = [
                    'id' => $allergen->id,
                    'sequence' => $allergen->sequence ?? null,
                    'parentId' => isset($allergen->parentId) ? $allergen->parentId : 0,
                    'name' => $this->getLocalizedValue($allergen->name ?? [], $language, ''),
                    'levelOfContainmentId' =>  isset($allergen->levelOfContainment->id) ? $allergen->levelOfContainment->id : 0,
                    'levelOfContainment' => $this->getLocalizedValue($allergen->levelOfContainment->name ?? [], $language, '')
                ];
            }
        } 

        return empty($allergens) ? null : $allergens;
    }

    /**
     * Renders allergen information in an HTML table format.
     *
     * @param array $allergens List of allergens
     * @param bool $extended Whether to show extended information
     * @param string $language Language code
     * @return string HTML representation of allergens
     */
    private function renderTableAllergens(array $allergens, bool $extended, string $language): string
    {
        $tableRows = '';
        $tableHeader = '';

        if ($extended == false) {
            foreach ($allergens as $allergen) {
                $tableRows .= sprintf(
                    '<td class="allergen-simple-td%s"><img loading="lazy" src="%s" title="%s" class="allergen-simple-icon"><div class="allergen-simple-text">%s</div></td>',
                    $allergen['levelOfContainmentId'] != 4 ? ' allergen-simple-excluded' : '',
                    $this->cdn . $allergen['id'] . '.png',
                    htmlspecialchars($allergen['levelOfContainment']),
                    htmlspecialchars($allergen['name'])
                );
            }

            return sprintf(
                '<table class="allergens-simple-table"><tbody><tr class="allergen-simple-tr">%s</tr></tbody></table>',
                $tableRows
            );
        } else {
            $labels = $this->translations[$language];
            $tableHeader = sprintf(
                '<tr><td class="allergen-header">%s</td><td class="allergen-header">%s</td><td class="allergen-header">%s</td><td class="allergen-header">%s</td></tr><tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                $labels['contains'],
                $labels['may_contain'],
                $labels['without'],
                $labels['not_specified'],
                $this->levelOfContainment[0],
                $this->levelOfContainment[1],
                $this->levelOfContainment[2],
                $this->levelOfContainment[3]
            );

            $mainAllergenes = [];
            $subAllergenes = [];

            foreach ($allergens as $allergen) {
                if (isset($allergen['parentId']) && $allergen['parentId'] == 0) {
                    $mainAllergenes[] = $allergen;
                } elseif (isset($allergen['parentId']) && isset($allergen['id'])) {
                    $subAllergenes[$allergen['parentId']][] = $allergen;
                }
            }

            $col1 = [];
            $col2 = [];
            $col3 = [];
            $itemCount = 0;

            foreach ($mainAllergenes as $mainAllergen) {
                $currentCol = ($itemCount < 10) ? 'col1' : (($itemCount < 20) ? 'col2' : 'col3');
                ${$currentCol}[] = $mainAllergen;
                $itemCount++;

                if (isset($mainAllergen['id']) && isset($subAllergenes[$mainAllergen['id']])) {
                    foreach ($subAllergenes[$mainAllergen['id']] as $subAllergen) {
                        $currentCol = ($itemCount < 10) ? 'col1' : (($itemCount < 20) ? 'col2' : 'col3');
                        ${$currentCol}[] = $subAllergen;
                        $itemCount++;
                    }
                }
            }

            $tableRows = '';
            $maxRows = max(count($col1), count($col2), count($col3));

            for ($i = 0; $i < $maxRows; $i++) {
                $tableRows .= '<tr class="allergen-tr">';

                foreach ([$col1, $col2, $col3] as $column) {
                    if (isset($column[$i])) {
                        $allergen = $column[$i];
                        $name = isset($allergen['name']) ? htmlspecialchars($allergen['name']) : '';
                        $levelId = isset($allergen['levelOfContainmentId']) ? $allergen['levelOfContainmentId'] : 0;
                        $icon = isset($this->levelOfContainment[$levelId]) ? $this->levelOfContainment[$levelId] : '';

                        $class = $allergen["parentId"] != 0 ? ' allergen-subitem' : '';
                        $tableRows .= '<td class="allergen-name'.$class.'">' . $name . '</td>';
                        $tableRows .= '<td class="allergen-levelofcontainment">' . $icon . '</td>';
                    } else {
                        $tableRows .= '<td></td><td></td>';
                    }
                }

                $tableRows .= '</tr>';
            }

            return sprintf(
                '<table class="allergens-table-legend"><tbody>%s</tbody></table>   <table class="allergens-table"><tbody>%s</tbody></table>',
                $tableHeader,
                $tableRows
            );
        }
    }

    /**
     * Renders allergen information in Bootstrap-compatible HTML format.
     *
     * @param array $allergens List of allergens
     * @param bool $extended Whether to show extended information
     * @param string $language Language code
     * @return string Bootstrap HTML representation of allergens
     */
    private function renderBootstrapAllergens(array $allergens, bool $extended, string $language): string
    {
        $tableRows = '';

        if ($extended == false) {
            foreach ($allergens as $allergen) {
                $excludedClass = $allergen['levelOfContainmentId'] != 4 ? ' allergen-excluded' : '';
                $tableRows .= sprintf(
                    '<div class="col text-center%s">
                        <div class="card h-100 border-0">
                            <img loading="lazy" src="%s" class="mx-auto" alt="%s" title="%s" style="width: 40px; height: 40px;">
                            <div class="card-body p-1">
                                <p class="card-text small">%s</p>
                            </div>
                        </div>
                    </div>',
                    $excludedClass,
                    $this->cdn . $allergen['id'] . '.png',
                    htmlspecialchars($allergen['name']),
                    htmlspecialchars($allergen['levelOfContainment']),
                    htmlspecialchars($allergen['name'])
                );
            }

            return sprintf(
                '<div class="container p-0">
                    <div class="row row-cols-4 row-cols-md-6 row-cols-lg-8 g-2">%s</div>
                </div>',
                $tableRows
            );
        } else { 
            $labels = $this->translations[$language];
            $legendHtml = '
                <div class="card mb-3">
                    <div class="card-body p-2">
                        <div class="row">
                            <div class="col-3 text-center">
                                <div><strong>' . $labels['contains'] . '</strong></div>
                                <div>' . $this->levelOfContainment[0] . '</div>
                            </div>
                            <div class="col-3 text-center">
                                <div><strong>' . $labels['may_contain'] . '</strong></div>
                                <div>' . $this->levelOfContainment[1] . '</div>
                            </div>
                            <div class="col-3 text-center">
                                <div><strong>' . $labels['without'] . '</strong></div>
                                <div>' . $this->levelOfContainment[2] . '</div>
                            </div>
                            <div class="col-3 text-center">
                                <div><strong>' . $labels['not_specified'] . '</strong></div>
                                <div>' . $this->levelOfContainment[3] . '</div>
                            </div>
                        </div>
                    </div>
                </div>';

            $mainAllergenes = [];
            $subAllergenes = [];

            foreach ($allergens as $allergen) {
                if (isset($allergen['parentId']) && $allergen['parentId'] == 0) {
                    $mainAllergenes[] = $allergen;
                } elseif (isset($allergen['parentId']) && isset($allergen['id'])) {
                    $subAllergenes[$allergen['parentId']][] = $allergen;
                }
            }

            $col1 = [];
            $col2 = [];
            $col3 = [];
            $itemCount = 0;

            foreach ($mainAllergenes as $mainAllergen) {
                $currentCol = ($itemCount < 10) ? 'col1' : (($itemCount < 20) ? 'col2' : 'col3');
                ${$currentCol}[] = $mainAllergen;
                $itemCount++;

                if (isset($mainAllergen['id']) && isset($subAllergenes[$mainAllergen['id']])) {
                    foreach ($subAllergenes[$mainAllergen['id']] as $subAllergen) {
                        $currentCol = ($itemCount < 10) ? 'col1' : (($itemCount < 20) ? 'col2' : 'col3');
                        ${$currentCol}[] = $subAllergen;
                        $itemCount++;
                    }
                }
            }

            $columnsHtml = '<div class="row">';

            foreach ([$col1, $col2, $col3] as $columnIndex => $column) {
                $columnHtml = '<div class="col-md-4">';

                foreach ($column as $allergen) {
                    $name = isset($allergen['name']) ? htmlspecialchars($allergen['name']) : '';
                    $levelId = isset($allergen['levelOfContainmentId']) ? $allergen['levelOfContainmentId'] : 0;
                    $icon = isset($this->levelOfContainment[$levelId]) ? $this->levelOfContainment[$levelId] : '';
                    $isSubItem = $allergen["parentId"] != 0;

                    $paddingClass = $isSubItem ? 'ps-3' : '';

                    $columnHtml .= sprintf(
                        '<div class="d-flex align-items-center mb-1 %s">
                            <div class="flex-grow-1">%s</div>
                            <div class="ms-2">%s</div>
                        </div>',
                        $paddingClass,
                        $name,
                        $icon
                    );
                }

                $columnHtml .= '</div>';
                $columnsHtml .= $columnHtml;
            }

            $columnsHtml .= '</div>';

            return $legendHtml . $columnsHtml;
        }
    }

    /**
     * Gets a localized value from an array of multilingual objects
     * 
     * @param array $values Array of multilingual value objects
     * @param string $language Language code to search for
     * @param string $default Default value if no match is found
     * @return string The localized value or default value
     */
    private function getLocalizedValue(array $values, string $language, string $default = ''): string
    {
        foreach ($values as $value) {
            if (isset($value->language) && $value->language === $language && isset($value->value)) {
                return $value->value;
            }
        }

        if (count($values) > 0 && isset($values[0]->value)) {
            return $values[0]->value;
        }

        return $default;
    }

    /**
     * Gets localized country of origin names
     * 
     * @param array $countries Array of country objects
     * @param string $language Language code
     * @return array Array of localized country names
     */
    private function getLocalizedCountryOfOrigins(array $countries, string $language): array
    {
        $result = [];

        foreach ($countries as $country) {
            if (isset($country->name) && is_array($country->name)) {
                $localizedName = $this->getLocalizedValue($country->name, $language, '');
                if (!empty($localizedName)) {
                    $result[] = $localizedName;
                }
            }
        }

        return $result;
    }
}