import { useBaseListing } from '@craftable/composables/useBaseListing.js';

export function useAppListing(props) {
    const baseListing = useBaseListing(props);
    // Add project-wide listing customizations here
    return { ...baseListing };
}
