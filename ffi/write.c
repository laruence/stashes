#include <stdlib.h>
#include <string.h>
#include "write.h"

size_t own_writefunc(void *ptr, size_t size, size_t nmember, void *data) {
	own_write_data *d = (own_write_data*)data;
	size_t total = size * nmember;

	if (d->buf == NULL) {
		d->buf = malloc(total);
		if (d->buf == NULL) {
			return 0;
		}
		d->size = total;
		memcpy(d->buf, ptr, total);
	} else {
		d->buf = realloc(d->buf, d->size + total);
		if (d->buf == NULL) {
			return 0;
		}
		memcpy(d->buf + d->size, ptr, total);
		d->size += total;
	}

	return total;
}	

void * init() {
	return &own_writefunc;
}
