#define FFI_LIB "write.so"
#define FFI_SCOPE "write"

typedef struct _writedata {
	void *buf;
	size_t size;
} own_write_data;

void *init();
