<?php

class SiteSelectorField extends SelectorField {

    /**
     * Get files based on types option.
     *
     * @since 1.0.0
     *
     * @return \Files
     */
    public function files()
    {
        /**
         * FIX: When used in site options, we don't have a `$this->page`
         * property we can use to access the pages files.
         *
         * (1) If we have page property, we'll use that to fetch the files.
         * (2) If we don't have a page property we're on the site options page.
         *     (2.1) If we're using Kirby 2.1+ we can use the new `site()->files()`
         *           method to get access to the new global site files to use them.
         *     (2.2) If we are using a lower version, global site files don't
         *           exist. We'll return an empty collection object instead.
         *
         * @since 1.3.0
         */
        
        $files = site()->files();

        /**
         * FIX: Create a new reference to $this to overcome the unavailability
         * of $this within closures in PHP < 5.4.0 by passing this new reference
         * with the "use" language construct.
         *
         * @since 1.0.1
         */
        $field = &$this;

        $files = $files->sortBy($this->sort, ($this->flip) ? 'desc' : 'asc')
            ->filter(function ($file) use ($field) {
                return $field->includeAllFiles() or in_array($file->type(), $field->types());
            });

        /**
         * Filter files using a regular expression.
         *
         * @since 1.4.0
         */
        if ($this->isRegExp($this->filter)) {
            $files = $files->filter(function ($file) use ($field) {
                return (preg_match($this->filter, $file->filename()) === 1);
            });
        }

        /**
         * Filter files by filename if a filter has been set.
         *
         * @since 1.3.0
         */
        elseif ($this->filter) {
            $files = $files->filterBy('filename', '*=', $this->filter);
        }

        return $files;
    }


}