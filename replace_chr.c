/* Copyright (C) 2020 laruence */
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <emmintrin.h>

static inline unsigned long long rdtsc(void) {
	unsigned int hi, lo;
	__asm__ __volatile__("rdtsc;rdtsc;\n" : "=a"(lo), "=d"(hi));

	return (unsigned long long)lo | ((unsigned long long)hi << 32);
}

static inline void replace_chr_normal(char *class_name, size_t class_name_len) {
	char *pos = class_name;
	size_t len = class_name_len;

	while ((pos = memchr(pos, '\\', len - (pos - class_name)))) {
		*pos++ = '_';
	}
	return;
}

static inline void replace_chr_sse2(char *class_name, size_t class_name_len) {
	char *pos = class_name;
	size_t len = class_name_len;
	const __m128i slash = _mm_set1_epi8('\\');
	const __m128i delta = _mm_set1_epi8('_' - '\\');

	while (len >= 16) {
		__m128i op = _mm_loadu_si128((__m128i *)pos);
		__m128i eq = _mm_cmpeq_epi8(op, slash);

		if (_mm_movemask_epi8(eq)) {
			eq = _mm_and_si128(eq, delta);
			op = _mm_add_epi8(op, eq);
			_mm_storeu_si128((__m128i*)pos, op);
		}
		len -= 16;
		pos += 16;
	}

	if (len) {
		replace_chr_normal(pos, len);
	}
}

static inline unsigned long long bench_replace_normal(char *mem, int capacity, int step) {
	unsigned long long int s;
	int i;

	// Warmup
	replace_chr_normal(mem, 1);

	s = rdtsc();
	for (i = step; i < capacity; i += step) {
		replace_chr_normal(mem, step);
		mem += step;
	}
	return rdtsc() - s;
}

static inline unsigned long long  bench_replace_sse2(char *mem, int capacity, int step) {
	unsigned long long int s;
	int i;

	// Warmup
	replace_chr_sse2(mem, 1);

	s = rdtsc();
	for (i = step; i < capacity; i += step) {
		replace_chr_sse2(mem, step);
		mem += step;
	}
	return rdtsc() - s;
}

static const char ascii[] = "01234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ\\_+_^%$#@!";

static char* prepare_string(int capacity) {
	char *str = malloc(sizeof(char) * capacity);
	srand(time(NULL));
	for (int i = 0; i < capacity; i++) {
		str[i] = ascii[rand() % (sizeof(ascii) - 1)];
	}
	return str;
}

int main(int argc, char **argv) {
	int i, capacity = 1024;
	unsigned long long normal, sse2;
	char *bak = prepare_string(capacity);
	char *str = malloc(sizeof(char) * capacity);

	printf("| Length |  Nomal |  SSE2  |  RAT |\n");
	printf("-----------------------------------\n");
	for (i = 4; i < 1024; i = i << 1) {
		memcpy(str, bak, capacity);
		normal = bench_replace_normal(str, capacity, i);
		memcpy(str, bak, capacity);
		sse2 = bench_replace_sse2(str, capacity, i);
		printf("| %6d | %6lld | %6lld | % 3.0lf%% |\n", i, normal, sse2, ((((double)sse2-(double)normal))/normal) * 100);
	}

	free(str);
	free(bak);
	return 0;
}
