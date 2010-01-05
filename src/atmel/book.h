#ifndef _BOOK_H_
#define _BOOK_H_

#include "global.h"

void bookInit( void );
void bookListBuild( const BYTE, BYTE * const);
void bookFind( BYTE * const , BYTE * const );
void bookFindMisplaced( void );
void bookCheckIn( void );
void bookCheckOut( void );
void bookModify( void );
void bookScan( void );

#endif /* _BOOK_H_ */

