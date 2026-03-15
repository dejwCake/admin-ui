import { useBaseForm } from '@craftable/composables/useBaseForm.js';

export function useAppForm(props, options = {}) {
    const baseForm = useBaseForm(props, options);
    // Add project-wide form customizations here
    return { ...baseForm };
}
