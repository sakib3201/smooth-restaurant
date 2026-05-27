import { useState, useEffect, useCallback, useRef, useMemo } from 'react';
import { __ } from '@wordpress/i18n';
import { cn } from '../lib/utils';
import { Button } from '@/admin/components/ui/button';
import { Input } from '@/admin/components/ui/input';
import { Label } from '@/admin/components/ui/label';
import { Badge } from '@/admin/components/ui/badge';
import { Checkbox } from '@/admin/components/ui/checkbox';
import {
	Select,
	SelectContent,
	SelectGroup,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from '@/admin/components/ui/select';
import {
	Dialog,
	DialogContent,
	DialogHeader,
	DialogTitle,
	DialogDescription,
	DialogFooter,
} from '@/admin/components/ui/dialog';
import {
	Table,
	TableBody,
	TableCell,
	TableHead,
	TableHeader,
	TableRow,
} from '@/admin/components/ui/table';
import {
	Alert,
	AlertTitle,
	AlertAction,
} from '@/admin/components/ui/alert';
import { Skeleton } from '@/admin/components/ui/skeleton';
import {
	Empty,
	EmptyHeader,
	EmptyTitle,
	EmptyDescription,
} from '@/admin/components/ui/empty';
import { X, Loader2 } from 'lucide-react';

interface Reservation {
	id: number;
	reservation_date: string;
	time: string;
	party_size: number;
	customer_name: string;
	customer_phone: string;
	customer_email?: string;
	special_requests?: string;
	status: string;
	created_at?: string;
}

interface ReservationsResponse {
	items: Reservation[];
	total: number;
	page: number;
	per_page: number;
	total_pages: number;
}

interface ApiError {
	message: string;
}

type StatusFilter = '' | 'pending' | 'confirmed' | 'cancelled' | 'no-show';
type BulkAction = '' | 'confirm' | 'cancel' | 'delete';

const STATUS_LABELS: Record<string, string> = {
	pending: __( 'Pending', 'smooth-restaurant' ),
	confirmed: __( 'Confirmed', 'smooth-restaurant' ),
	cancelled: __( 'Cancelled', 'smooth-restaurant' ),
	'no-show': __( 'No Show', 'smooth-restaurant' ),
};

const STATUS_VARIANTS: Record<string, React.ComponentProps<typeof Badge>['variant']> = {
	pending: 'secondary',
	confirmed: 'default',
	cancelled: 'destructive',
	'no-show': 'outline',
};

const VALID_TRANSITIONS: Record<string, string[]> = {
	pending: [ 'confirmed', 'cancelled' ],
	confirmed: [ 'cancelled', 'no-show' ],
	cancelled: [],
	'no-show': [],
};

function getToday(): string {
	return new Date().toISOString().split( 'T' )[ 0 ];
}

function getDefaultDateTo(): string {
	const d = new Date();
	d.setDate( d.getDate() + 30 );
	return d.toISOString().split( 'T' )[ 0 ];
}

function apiFetch<T>( url: string, options?: RequestInit ): Promise<T> {
	return fetch( url, {
		...options,
		headers: {
			'Content-Type': 'application/json',
			Accept: 'application/json',
			'X-WP-Nonce': ( window as any ).smoothRestaurantAdmin?.restNonce || '',
			...( options?.headers || {} ),
		},
		credentials: 'same-origin',
	} ).then( async ( res ) => {
		if ( ! res.ok ) {
			let err: ApiError = { message: __( 'Request failed.', 'smooth-restaurant' ) };
			try {
				const body = await res.json();
				err = body.message ? { message: body.message } : body;
			} catch {
				// ignore parse error
			}
			throw new Error( err.message || __( 'Request failed.', 'smooth-restaurant' ) );
		}
		return res.json() as Promise<T>;
	} );
}

function buildQueryString( params: Record<string, string | number> ): string {
	const qs = new URLSearchParams();
	Object.entries( params ).forEach( ( [ k, v ] ) => {
		if ( v !== '' && v !== undefined && v !== null ) {
			qs.append( k, String( v ) );
		}
	} );
	return qs.toString();
}

export const Reservations = () => {
	const restUrl = ( ( window as any ).smoothRestaurantAdmin?.restUrl || '/wp-json/' ).replace( /\/$/, '' );

	const [ items, setItems ] = useState<Reservation[]>( [] );
	const [ total, setTotal ] = useState( 0 );
	const [ page, setPage ] = useState( 1 );
	const [ perPage, setPerPage ] = useState( 20 );
	const [ totalPages, setTotalPages ] = useState( 1 );
	const [ statusFilter, setStatusFilter ] = useState<StatusFilter>( '' );
	const [ dateFrom, setDateFrom ] = useState( getToday() );
	const [ dateTo, setDateTo ] = useState( getDefaultDateTo() );
	const [ loading, setLoading ] = useState( false );
	const [ error, setError ] = useState<string | null>( null );
	const [ selectedIds, setSelectedIds ] = useState<Set<number>>( new Set() );
	const [ bulkAction, setBulkAction ] = useState<BulkAction>( '' );
	const [ detailModal, setDetailModal ] = useState<Reservation | null>( null );
	const [ updatingId, setUpdatingId ] = useState<number | null>( null );
	const abortRef = useRef<AbortController | null>( null );

	const fetchReservations = useCallback( () => {
		setLoading( true );
		setError( null );
		if ( abortRef.current ) {
			abortRef.current.abort();
		}
		const controller = new AbortController();
		abortRef.current = controller;

		const qs = buildQueryString( {
			page,
			per_page: perPage,
			status: statusFilter,
			date_from: dateFrom,
			date_to: dateTo,
			orderby: 'reservation_date',
			order: 'ASC',
		} );

		apiFetch<ReservationsResponse>( `${ restUrl }/smooth-restaurant/v1/reservations?${ qs }`, {
			signal: controller.signal,
		} )
			.then( ( data ) => {
				setItems( data.items || [] );
				setTotal( data.total || 0 );
				setTotalPages( data.total_pages || 1 );
				setSelectedIds( new Set() );
			} )
			.catch( ( err: unknown ) => {
				if ( err instanceof Error && err.name === 'AbortError' ) {
					return;
				}
				setError( err instanceof Error ? err.message : __( 'Unknown error', 'smooth-restaurant' ) );
			} )
			.finally( () => {
				setLoading( false );
			} );
	}, [ restUrl, page, perPage, statusFilter, dateFrom, dateTo ] );

	useEffect( () => {
		fetchReservations();
		return () => {
			if ( abortRef.current ) {
				abortRef.current.abort();
			}
		};
	}, [ fetchReservations ] );

	const toggleSelectAll = useCallback( () => {
		if ( selectedIds.size === items.length && items.length > 0 ) {
			setSelectedIds( new Set() );
		} else {
			setSelectedIds( new Set( items.map( ( i ) => i.id ) ) );
		}
	}, [ items, selectedIds.size ] );

	const toggleSelectOne = useCallback( ( id: number ) => {
		setSelectedIds( ( prev ) => {
			const next = new Set( prev );
			if ( next.has( id ) ) {
				next.delete( id );
			} else {
				next.add( id );
			}
			return next;
		} );
	}, [] );

	const updateStatus = useCallback(
		async ( id: number, status: string ) => {
			setUpdatingId( id );
			try {
				await apiFetch( `${ restUrl }/smooth-restaurant/v1/reservations/${ id }`, {
					method: 'PATCH',
					body: JSON.stringify( { status } ),
				} );
				setItems( ( prev ) =>
					prev.map( ( item ) => ( item.id === id ? { ...item, status } : item ) )
				);
				if ( detailModal && detailModal.id === id ) {
					setDetailModal( { ...detailModal, status } );
				}
			} catch ( err: unknown ) {
				const message = err instanceof Error ? err.message : __( 'Update failed', 'smooth-restaurant' );
				setError( message );
			} finally {
				setUpdatingId( null );
			}
		},
		[ restUrl, detailModal ]
	);

	const handleBulkAction = useCallback( async () => {
		if ( ! bulkAction || selectedIds.size === 0 ) {
			return;
		}
		const ids = Array.from( selectedIds );
		if ( bulkAction === 'delete' ) {
			const ok = window.confirm(
				__( 'Are you sure you want to delete the selected reservations?', 'smooth-restaurant' )
			);
			if ( ! ok ) {
				return;
			}
		}

		setLoading( true );
		try {
			await apiFetch( `${ restUrl }/smooth-restaurant/v1/reservations/bulk`, {
				method: 'POST',
				body: JSON.stringify( { action: bulkAction, ids } ),
			} );
			setSelectedIds( new Set() );
			setBulkAction( '' );
			fetchReservations();
		} catch ( err: unknown ) {
			const message = err instanceof Error ? err.message : __( 'Bulk action failed', 'smooth-restaurant' );
			setError( message );
		} finally {
			setLoading( false );
		}
	}, [ bulkAction, selectedIds, restUrl, fetchReservations ] );

	const isAllSelected = items.length > 0 && selectedIds.size === items.length;

	const statusOptions = useMemo(
		() => [
			{ value: '', label: __( 'All', 'smooth-restaurant' ) },
			{ value: 'pending', label: __( 'Pending', 'smooth-restaurant' ) },
			{ value: 'confirmed', label: __( 'Confirmed', 'smooth-restaurant' ) },
			{ value: 'cancelled', label: __( 'Cancelled', 'smooth-restaurant' ) },
			{ value: 'no-show', label: __( 'No Show', 'smooth-restaurant' ) },
		],
		[]
	);

	const bulkOptions = useMemo(
		() => [
			{ value: '', label: __( 'Bulk actions', 'smooth-restaurant' ) },
			{ value: 'confirm', label: __( 'Confirm', 'smooth-restaurant' ) },
			{ value: 'cancel', label: __( 'Cancel', 'smooth-restaurant' ) },
			{ value: 'delete', label: __( 'Delete', 'smooth-restaurant' ) },
		],
		[]
	);

	const perPageOptions = useMemo(
		() => [
			{ value: '10', label: '10' },
			{ value: '20', label: '20' },
			{ value: '50', label: '50' },
		],
		[]
	);

	return (
		<div className="sr-flex sr-flex-col sr-gap-4">
			{ error && (
				<Alert variant="destructive" role="alert">
					<AlertTitle>{ error }</AlertTitle>
					<AlertAction>
						<Button variant="ghost" size="sm" onClick={ () => setError( null ) }>
							<X className="sr-size-4" />
							<span className="sr-sr-only">{ __( 'Dismiss', 'smooth-restaurant' ) }</span>
						</Button>
					</AlertAction>
				</Alert>
			) }

			{/* Filters */}
			<div className="sr-flex sr-flex-col sm:sr-flex-row sr-gap-3 sr-items-start sm:sr-items-end">
				<div className="sr-flex sr-flex-col sr-gap-1.5">
					<Label htmlFor="date-from" className="sr-text-label">{ __( 'From', 'smooth-restaurant' ) }</Label>
					<Input
						id="date-from"
						type="date"
						value={ dateFrom }
						onChange={ ( e ) => { setDateFrom( e.target.value ); setPage( 1 ); } }
					/>
				</div>
				<div className="sr-flex sr-flex-col sr-gap-1.5">
					<Label htmlFor="date-to" className="sr-text-label">{ __( 'To', 'smooth-restaurant' ) }</Label>
					<Input
						id="date-to"
						type="date"
						value={ dateTo }
						onChange={ ( e ) => { setDateTo( e.target.value ); setPage( 1 ); } }
					/>
				</div>
				<div className="sr-flex sr-flex-col sr-gap-1.5">
					<Label htmlFor="status-filter" className="sr-text-label">{ __( 'Status', 'smooth-restaurant' ) }</Label>
					<Select
						value={ statusFilter }
						onValueChange={ ( v ) => { setStatusFilter( v as StatusFilter ); setPage( 1 ); } }
					>
						<SelectTrigger id="status-filter" className="sr-w-[180px]">
							<SelectValue placeholder={ __( 'All', 'smooth-restaurant' ) } />
						</SelectTrigger>
						<SelectContent>
							<SelectGroup>
								{ statusOptions.map( ( opt ) => (
									<SelectItem key={ opt.value } value={ opt.value }>{ opt.label }</SelectItem>
								) ) }
							</SelectGroup>
						</SelectContent>
					</Select>
				</div>
				<Button onClick={ fetchReservations } disabled={ loading } className="sr-self-end">
					{ loading ? <Loader2 className="sr-animate-spin" /> : __( 'Filter', 'smooth-restaurant' ) }
				</Button>
			</div>

			{/* Bulk actions */}
			<div className="sr-flex sr-flex-col sm:sr-flex-row sr-gap-3 sr-items-start sm:sr-items-center sr-justify-between">
				<div className="sr-flex sr-gap-2 sr-items-center">
					<Select
						value={ bulkAction }
						onValueChange={ ( v ) => setBulkAction( v as BulkAction ) }
					>
						<SelectTrigger className="sr-w-[180px]">
							<SelectValue placeholder={ __( 'Bulk actions', 'smooth-restaurant' ) } />
						</SelectTrigger>
						<SelectContent>
							<SelectGroup>
								{ bulkOptions.map( ( opt ) => (
									<SelectItem key={ opt.value } value={ opt.value }>{ opt.label }</SelectItem>
								) ) }
							</SelectGroup>
						</SelectContent>
					</Select>
					<Button
						variant="outline"
						onClick={ handleBulkAction }
						disabled={ ! bulkAction || selectedIds.size === 0 || loading }
					>
						{ __( 'Apply', 'smooth-restaurant' ) }
					</Button>
				</div>
				<span className="sr-text-label sr-text-sr-text-secondary">
					{ total } { __( 'reservations', 'smooth-restaurant' ) }
				</span>
			</div>

			{/* Table */}
			<div className="sr-rounded-sr-md sr-border sr-border-sr-border sr-overflow-hidden">
				<Table>
					<TableHeader>
						<TableRow>
							<TableHead className="sr-w-10">
								<Checkbox
									checked={ isAllSelected }
									onCheckedChange={ toggleSelectAll }
									aria-label={ __( 'Select all', 'smooth-restaurant' ) }
								/>
							</TableHead>
							<TableHead>{ __( 'ID', 'smooth-restaurant' ) }</TableHead>
							<TableHead>{ __( 'Date', 'smooth-restaurant' ) }</TableHead>
							<TableHead>{ __( 'Time', 'smooth-restaurant' ) }</TableHead>
							<TableHead>{ __( 'Party', 'smooth-restaurant' ) }</TableHead>
							<TableHead>{ __( 'Name', 'smooth-restaurant' ) }</TableHead>
							<TableHead>{ __( 'Phone', 'smooth-restaurant' ) }</TableHead>
							<TableHead>{ __( 'Status', 'smooth-restaurant' ) }</TableHead>
							<TableHead className="sr-text-right">{ __( 'Actions', 'smooth-restaurant' ) }</TableHead>
						</TableRow>
					</TableHeader>
					<TableBody>
						{ items.length === 0 && ! loading && (
							<TableRow>
								<TableCell colSpan={ 9 }>
									<Empty>
										<EmptyHeader>
											<EmptyTitle>{ __( 'No reservations found.', 'smooth-restaurant' ) }</EmptyTitle>
										</EmptyHeader>
									</Empty>
								</TableCell>
							</TableRow>
						) }
						{ items.map( ( item ) => (
							<TableRow key={ item.id }>
								<TableCell>
									<Checkbox
										checked={ selectedIds.has( item.id ) }
										onCheckedChange={ () => toggleSelectOne( item.id ) }
										aria-label={ __( 'Select reservation', 'smooth-restaurant' ) }
									/>
								</TableCell>
								<TableCell className="sr-font-medium">#{ item.id }</TableCell>
								<TableCell>{ item.reservation_date }</TableCell>
								<TableCell>{ item.time }</TableCell>
								<TableCell>{ item.party_size }</TableCell>
								<TableCell className="sr-font-medium">{ item.customer_name }</TableCell>
								<TableCell>{ item.customer_phone }</TableCell>
								<TableCell>
									<Badge variant={ STATUS_VARIANTS[ item.status ] || 'secondary' }>
										{ STATUS_LABELS[ item.status ] || item.status }
									</Badge>
								</TableCell>
								<TableCell className="sr-text-right">
									<div className="sr-flex sr-justify-end sr-gap-2">
										{ ( VALID_TRANSITIONS[ item.status ] || [] ).map( ( nextStatus ) => (
											<Button
												key={ nextStatus }
												size="sm"
												variant={ nextStatus === 'cancelled' ? 'destructive' : 'default' }
												onClick={ () => updateStatus( item.id, nextStatus ) }
												disabled={ updatingId === item.id }
											>
												{ STATUS_LABELS[ nextStatus ] || nextStatus }
											</Button>
										) ) }
										<Button
											variant="outline"
											size="sm"
											onClick={ () => setDetailModal( item ) }
										>
											{ __( 'View', 'smooth-restaurant' ) }
										</Button>
									</div>
								</TableCell>
							</TableRow>
						) ) }
						{ loading && items.length === 0 && (
							<TableRow>
								<TableCell colSpan={ 9 }>
									<div className="sr-flex sr-items-center sr-justify-center sr-py-8 sr-gap-2">
										<Loader2 className="sr-animate-spin sr-size-5 sr-text-sr-text-secondary" />
										<span className="sr-text-sm sr-text-sr-text-secondary">{ __( 'Loading…', 'smooth-restaurant' ) }</span>
									</div>
								</TableCell>
							</TableRow>
						) }
					</TableBody>
				</Table>
			</div>

			{/* Pagination */}
			{ totalPages > 1 && (
				<div className="sr-flex sr-flex-col sm:sr-flex-row sr-items-center sr-justify-between sr-gap-3">
					<div className="sr-flex sr-items-center sr-gap-2">
						<Button
							variant="outline"
							size="sm"
							onClick={ () => setPage( ( p ) => Math.max( 1, p - 1 ) ) }
							disabled={ page <= 1 || loading }
						>
							{ __( 'Previous', 'smooth-restaurant' ) }
						</Button>
						<span className="sr-text-sm sr-text-sr-text-secondary">
							{ __( 'Page', 'smooth-restaurant' ) } { page } { __( 'of', 'smooth-restaurant' ) } { totalPages }
						</span>
						<Button
							variant="outline"
							size="sm"
							onClick={ () => setPage( ( p ) => Math.min( totalPages, p + 1 ) ) }
							disabled={ page >= totalPages || loading }
						>
							{ __( 'Next', 'smooth-restaurant' ) }
						</Button>
					</div>
					<div className="sr-flex sr-items-center sr-gap-2">
						<Label className="sr-text-sm sr-text-sr-text-secondary">{ __( 'Per page', 'smooth-restaurant' ) }</Label>
						<Select
							value={ String( perPage ) }
							onValueChange={ ( v ) => { setPerPage( parseInt( v, 10 ) ); setPage( 1 ); } }
						>
							<SelectTrigger className="sr-w-[80px]">
								<SelectValue />
							</SelectTrigger>
							<SelectContent>
								<SelectGroup>
									{ perPageOptions.map( ( opt ) => (
										<SelectItem key={ opt.value } value={ opt.value }>{ opt.label }</SelectItem>
									) ) }
								</SelectGroup>
							</SelectContent>
						</Select>
					</div>
				</div>
			) }

			{/* Detail Modal */}
			<Dialog open={ !! detailModal } onOpenChange={ ( open ) => ! open && setDetailModal( null ) }>
				<DialogContent className="sr-max-w-lg">
					<DialogHeader>
						<DialogTitle>
							{ detailModal?.customer_name } — { __( 'Reservation', 'smooth-restaurant' ) } #{ detailModal?.id }
						</DialogTitle>
						<DialogDescription>
							<Badge variant={ STATUS_VARIANTS[ detailModal?.status || '' ] || 'secondary' }>
								{ STATUS_LABELS[ detailModal?.status || '' ] || detailModal?.status }
							</Badge>
						</DialogDescription>
					</DialogHeader>
					<div className="sr-grid sr-grid-cols-2 sr-gap-4 sr-text-sm">
						<div>
							<span className="sr-font-medium sr-text-sr-text-secondary">{ __( 'Date', 'smooth-restaurant' ) }</span>
							<p>{ detailModal?.reservation_date }</p>
						</div>
						<div>
							<span className="sr-font-medium sr-text-sr-text-secondary">{ __( 'Time', 'smooth-restaurant' ) }</span>
							<p>{ detailModal?.time }</p>
						</div>
						<div>
							<span className="sr-font-medium sr-text-sr-text-secondary">{ __( 'Party Size', 'smooth-restaurant' ) }</span>
							<p>{ detailModal?.party_size }</p>
						</div>
						<div>
							<span className="sr-font-medium sr-text-sr-text-secondary">{ __( 'Phone', 'smooth-restaurant' ) }</span>
							<p>{ detailModal?.customer_phone }</p>
						</div>
					</div>
					<div className="sr-mt-4 sr-text-sm">
						<span className="sr-font-medium sr-text-sr-text-secondary">{ __( 'Email', 'smooth-restaurant' ) }</span>
						<p>{ detailModal?.customer_email || '—' }</p>
					</div>
					<div className="sr-mt-4 sr-text-sm">
						<span className="sr-font-medium sr-text-sr-text-secondary">{ __( 'Special Requests', 'smooth-restaurant' ) }</span>
						<p>{ detailModal?.special_requests || __( 'None', 'smooth-restaurant' ) }</p>
					</div>
					{ detailModal?.created_at && (
						<div className="sr-mt-4 sr-text-sm">
							<span className="sr-font-medium sr-text-sr-text-secondary">{ __( 'Submitted', 'smooth-restaurant' ) }</span>
							<p>{ detailModal.created_at }</p>
						</div>
					) }
					<DialogFooter className="sr-gap-2">
						{ ( VALID_TRANSITIONS[ detailModal?.status || '' ] || [] ).map( ( nextStatus ) => (
							<Button
								key={ nextStatus }
								variant={ nextStatus === 'cancelled' ? 'destructive' : 'default' }
								onClick={ () => detailModal && updateStatus( detailModal.id, nextStatus ) }
								disabled={ updatingId === detailModal?.id }
							>
								{ STATUS_LABELS[ nextStatus ] || nextStatus }
							</Button>
						) ) }
					</DialogFooter>
				</DialogContent>
			</Dialog>
		</div>
	);
};

export default Reservations;
