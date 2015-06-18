<?php

/**
 * Implementation of hook_permission().
 */
function mysearch_permission()
{
    /**
     *  1. Using t() function will allow multilingual administrators to understand title and description of access rights
     */
    return array(
        'access mysearch' => array(
            'title' => 'Access My Search',
            'description' => 'Allows a user to access search results',
        )
    );
}

/**
 * Implementation of hook_menu().
 */
function mysearch_menu()
{
    $items['mysearch'] = array(
        'title' => 'Search',
        'page callback' => 'mysearch_searchpage',
        'access arguments' => array('access mysearch'),
        'type' => MENU_SUGGESTED_ITEM,
    );
    return $items;
}

/**
 * Menu callback provides a simple list of nodes matching the
 * search term Example: hitting the URL:
 *   http://domain.com/mysearch/example
 * will return a list of links to nodes which have the word
 * example in them.
 */
function mysearch_searchpage()
{
    /**
     *  1. We need to check for empty values, otherwise it gives us all possible nodes
     */

    $searchterm = arg(1);

    /**
     * 1. Wrong table name 'node_revision'
     * 2. We might use figured braces {node}
     * 3. Placeholders in query like 'WHERE body LIKE :body', current query opened for sql injection
     * 4. body - not correct field. Each field (field_name) is stored in separate table
     * 5. User will receive the results including the nodes, which he does not have access to
     * 6. And of course we must limit results (pager)
     */
    $query = "
     SELECT nid
     FROM node_revisions
     WHERE nid LIKE '%$searchterm%'
     ";

    /**
     * 1. It would be better to use dynamic queries instead of static, they are a little slower, but more useful
     *  - using extenders (pager, tablesort for example)
     *  - queries are alterable by other modules
     *  - camelcase features
     *  - easy to write big queries
     *
     * 2. Or use EntityFieldQuery instead of the one working directly with database
     *
     * 3. Also we need to fetch the result
     *
     */

    $search_results = db_query($query);

     /**
     * 1. It's a bad practice to combine output with calculations
     * 2. What about multilingual site? t() function can be helpful
     */

    $search_results = "<h2>Search for $searchterm</h2><ul>";

    /**
     * 1. We don't have $get_node_result variable
     * 2. Also it would be better to get all nid's and use node_load_multiple to load bunch of nodes
     */
    foreach ($get_node_result as $record)) {
    $node = node_load($record->nid);

    /**
     * 1. Create links using l($node->title, 'node/' . $node->nid) function
     * 2. theme('item_list', array('items' => $links, 'title' => t('Search for @searchterm', ['@searchterm' => $searchterm])));
     * 3. best practice to theme results by its own template
     */

    $search_results .= '<li><a href="/node/'
        . $node->nid . '" title="' . $node->title . '">'
        . $node->title . '</a><!-- debugging output: '
        . print_r($node) . '  --><\li>';
}
  return $search_results;
}