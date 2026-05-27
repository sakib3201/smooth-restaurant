import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

/**
 * Combines clsx and tailwind-merge for conditional class merging.
 *
 * @param inputs - Class values to merge.
 * @returns Merged class string.
 */
export function cn( ...inputs: ClassValue[] ) {
	return twMerge( clsx( inputs ) );
}
