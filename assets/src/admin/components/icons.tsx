import type { CSSProperties } from 'react';

interface IconProps {
	className?: string;
	style?: CSSProperties;
}

const svgProps = {
	xmlns: 'http://www.w3.org/2000/svg',
	width: 20,
	height: 20,
	viewBox: '0 0 24 24',
	fill: 'none',
	stroke: 'currentColor',
	strokeWidth: 2,
	strokeLinecap: 'round' as const,
	strokeLinejoin: 'round' as const,
};

export const DashboardIcon = ( { className }: IconProps ) => (
	<svg { ...svgProps } className={ className }>
		<rect width="7" height="9" x="3" y="3" rx="1" />
		<rect width="7" height="5" x="14" y="3" rx="1" />
		<rect width="7" height="9" x="14" y="12" rx="1" />
		<rect width="7" height="5" x="3" y="16" rx="1" />
	</svg>
);

export const MenuItemsIcon = ( { className }: IconProps ) => (
	<svg { ...svgProps } className={ className }>
		<path d="M3 7v12h13" />
		<path d="m19 15-3-3-4 4-3-3" />
	</svg>
);

export const OrdersIcon = ( { className }: IconProps ) => (
	<svg { ...svgProps } className={ className }>
		<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
		<path d="M3 6h18" />
		<path d="M16 10a4 4 0 0 1-8 0" />
	</svg>
);

export const ReservationsIcon = ( { className }: IconProps ) => (
	<svg { ...svgProps } className={ className }>
		<path d="M8 2v4" />
		<path d="M16 2v4" />
		<rect width="18" height="18" x="3" y="4" rx="2" />
		<path d="M3 10h18" />
	</svg>
);

export const SettingsIcon = ( { className }: IconProps ) => (
	<svg { ...svgProps } className={ className }>
		<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
		<circle cx="12" cy="12" r="3" />
	</svg>
);

export const LogoIcon = ( { className }: IconProps ) => (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		width={ 18 }
		height={ 18 }
		viewBox="0 0 24 24"
		fill="none"
		stroke="white"
		strokeWidth={ 2 }
		strokeLinecap="round"
		strokeLinejoin="round"
		className={ className }
	>
		<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2" />
		<path d="M7 2v20" />
		<path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7" />
	</svg>
);

export const HamburgerIcon = ( { className }: IconProps ) => (
	<svg { ...svgProps } className={ className }>
		<path d="M4 12h16" />
		<path d="M4 18h16" />
		<path d="M4 6h16" />
	</svg>
);

export const ChevronLeftIcon = ( { className, style }: IconProps ) => (
	<svg { ...svgProps } className={ className } style={ style }>
		<path d="m11 17-5-5 5-5" />
		<path d="m18 17-5-5 5-5" />
	</svg>
);

export const WordPressIcon = ( { className, style }: IconProps ) => (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		width={ 20 }
		height={ 20 }
		viewBox="0 0 24 24"
		fill="currentColor"
		className={ className }
		style={ style }
	>
		<path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 19.5c-5.239 0-9.5-4.261-9.5-9.5S6.761 2.5 12 2.5s9.5 4.261 9.5 9.5-4.261 9.5-9.5 9.5z" />
		<path d="M3.009 12c0 3.559 2.068 6.634 5.067 8.092L3.788 8.341A9.937 9.937 0 0 0 3.009 12zm18.139-.339c0-1.112-.4-1.881-.743-2.48-.458-.743-.889-1.371-.889-2.115 0-.828.628-1.603 1.512-1.603.04 0 .078.005.116.007A9.963 9.963 0 0 0 12 2.009c-2.779 0-5.236 1.064-7.115 2.72.2.006.389.01.55.01 1.158 0 2.954-.141 2.954-.141.596-.035.666.841.071.913 0 0-.599.071-1.267.106l4.033 11.989 2.423-7.265-1.723-4.724c-.596-.035-1.16-.106-1.16-.106-.596-.035-.526-.948.07-.913 0 0 1.831.141 2.989.141 1.158 0 2.954-.141 2.954-.141.596-.035.666.841.071.913 0 0-.599.071-1.267.106l3.998 11.889 1.104-3.684c.481-1.53.743-2.628.743-3.586z" />
	</svg>
);

export const BellIcon = ( { className, style }: IconProps ) => (
	<svg { ...svgProps } className={ className } style={ style }>
		<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
		<path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
	</svg>
);

export const SunIcon = ( { className, style }: IconProps ) => (
	<svg { ...svgProps } className={ className } style={ style }>
		<circle cx="12" cy="12" r="4" />
		<path d="M12 2v2" />
		<path d="M12 20v2" />
		<path d="m4.93 4.93 1.41 1.41" />
		<path d="m17.66 17.66 1.41 1.41" />
		<path d="M2 12h2" />
		<path d="M20 12h2" />
		<path d="m6.34 17.66-1.41 1.41" />
		<path d="m19.07 4.93-1.41 1.41" />
	</svg>
);

export const MoonIcon = ( { className, style }: IconProps ) => (
	<svg { ...svgProps } className={ className } style={ style }>
		<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
	</svg>
);

export const ShortcodeIcon = ( { className }: IconProps ) => (
	<svg { ...svgProps } className={ className }>
		<path d="M16 18l6-6-6-6" />
		<path d="M8 6l-6 6 6 6" />
	</svg>
);
